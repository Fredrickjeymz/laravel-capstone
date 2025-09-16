<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\StudentAssessmentScore;
use App\Models\StudentAssessmentQuestionScore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory as PptIOFactory;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class AssessmentScoringController extends Controller
{
    public function evaluateAnswers(Request $request)
    {

    ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
    ini_set('memory_limit', '512M');    // optional, increase if needed
    set_time_limit(300);  

    Log::info('Starting evaluateAnswers', ['request' => $request->except('answer_file')]);

    $request->validate([
        'assessment_id' => 'required|exists:assessments,id',
    ]);

    try {
        $file = $request->file('answer_file');
        $studentAnswers = $this->extractTextFromFile($file);

        if (empty(trim($studentAnswers))) {
            throw new \Exception("Answer file is empty or unreadable.");
        }

        $assessment = Assessment::with('questions')->findOrFail($request->assessment_id);

        $evaluationData = [
            'assessment' => $assessment,
            'student_answers' => $studentAnswers,
            'question_type' => $assessment->question_type
        ];

        $aiResponse = $this->callEvaluationAI($evaluationData);

        // Validate AI response structure
        if (!isset($aiResponse['total_score'], $aiResponse['max_score'], $aiResponse['percentage'], $aiResponse['question_results']) || !is_array($aiResponse['question_results'])) {
            throw new \Exception("Incomplete or invalid AI response.");
        }

        $filePath = $file->store('student_answers');

        // Save overall score
        $score = StudentAssessmentScore::create([
            'assessment_id' => $assessment->id,
            'class_id'      => $request->class_id,
            'student_name' => $request->student_name,
            'total_score' => $aiResponse['total_score'],
            'max_score' => $aiResponse['max_score'],
            'percentage' => $aiResponse['percentage'],
            'remarks' => $aiResponse['overall_feedback'] ?? null
        ]);

        // Save question-level scores
        $questions = $assessment->questions->values(); // Ensure it's a numerically indexed collection

        foreach ($aiResponse['question_results'] as $index => $result) {
            if (!isset($questions[$index])) {
                Log::warning("Mismatch: No question at index {$index}");
                continue;
            }
        
            StudentAssessmentQuestionScore::create([
                'student_assessment_score_id' => $score->id,
                'assessment_question_id' => $questions[$index]->id,
                'student_answer' => $result['student_answer'] ?? '',
                'score_given' => $result['score'] ?? 0,
                'max_score' => $result['max_score'] ?? 1,
                'criteria_scores' => isset($result['criteria_scores']) ? json_encode($result['criteria_scores']) : null,
                'feedback' => $result['feedback'] ?? null,
            ]);
        }
        
        

        return response()->json(['redirect' => route('scoring-result', $score->id)]);

    } catch (\Exception $e) {
        Log::error('Evaluation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Evaluation failed: ' . $e->getMessage()
        ], 500);
    }
}

private function callEvaluationAI($data)
{
    Log::info('Building AI evaluation prompt (with batching)');

    $questions = $data['assessment']->questions->values(); // Ensure numeric keys
    $batchSize = 5; // 🔹 fixed size
    $batches = ceil($questions->count() / $batchSize);

    $allResults = [
        'total_score' => 0,
        'max_score'   => 0,
        'question_results' => [],
    ];

    // 🔹 Split student answers into lines for easier mapping
    $studentAnswerLines = preg_split("/\r\n|\n|\r/", trim($data['student_answers']));

    for ($i = 0; $i < $batches; $i++) {
        $batchQuestions = $questions->slice($i * $batchSize, $batchSize);

        // 🔹 Try to map student answers by sequence_number
        $batchAnswers = "";
        foreach ($batchQuestions as $q) {
            foreach ($studentAnswerLines as $line) {
                if (preg_match('/^' . $q->sequence_number . '\./', $line)) {
                    $batchAnswers .= $line . "\n";
                }
            }
        }

        // 🔹 Build prompt for this batch
        $prompt = "Evaluate student answers against the following assessment:\n\n";
        $prompt .= "ASSESSMENT TITLE: {$data['assessment']->title}\n";
        $prompt .= "QUESTION TYPE: {$data['question_type']}\n\n";

        foreach ($batchQuestions as $q) {
            $prompt .= "QUESTION {$q->sequence_number}:\n{$q->question_text}\n";
            $prompt .= "CORRECT ANSWER: {$q->answer_key}\n\n";
        }

        if ($data['assessment']->rubric) {
            $prompt .= "RUBRIC:\n{$data['assessment']->rubric}\n\n";
        }

        $prompt .= "STUDENT ANSWERS (only for these questions):\n{$batchAnswers}\n\n";
        $prompt .= "INSTRUCTIONS:\n";
        $prompt .= "- Return JSON with total_score, max_score, percentage, and question_results.\n";
        $prompt .= "- Each question_results[] must have question_id, student_answer, score, max_score, feedback.\n";
        $prompt .= "- For multiple choice questions, student_answer must be only the letter (A, B, C, or D).\n";
        $prompt .= "- For subjective: include rubric-based criteria_scores.\n";
        $prompt .= "- JSON only. No extra explanation. Do NOT include markdown like ```json.\n\n";
        $prompt .= "JSON ONLY OUTPUT:";

        try {
        $response = retry(3, function () use ($prompt) {
            return Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ])
            ->timeout(120)           // how long to wait for full response
            ->connectTimeout(30)     // how long to wait for connection
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict but fair teacher evaluating student work.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
                'max_tokens' => 1500,
            ]);
        }, 2000);

            if ($response->failed()) {
                throw new \Exception("OpenAI API failed (batch $i) with status " . $response->status());
            }

            $content = $response->json('choices.0.message.content');
            if (!$content) {
                throw new \Exception("AI returned empty content (batch $i).");
            }

            $content = trim($content);
            $content = preg_replace('/^```(?:json)?\s*([\s\S]*?)\s*```$/', '$1', $content);
            $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);

            $result = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON (batch $i): " . json_last_error_msg());
            }

            // 🔹 Merge results
            $allResults['total_score'] += $result['total_score'] ?? 0;
            $allResults['max_score']   += $result['max_score'] ?? 0;

            foreach ($result['question_results'] as $qr) {
                $allResults['question_results'][] = $qr;
            }

        } catch (\Exception $e) {
            Log::error('AI call failed (batch)', ['batch' => $i, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    // 🔹 Compute overall percentage
    $allResults['percentage'] = $allResults['max_score'] > 0
        ? round(($allResults['total_score'] / $allResults['max_score']) * 100, 2)
        : 0;

    return $allResults;
}

public function scoringResult($id)
{
    try {
        $score = StudentAssessmentScore::with(['questionScores.question', 'assessment.questions'])->findOrFail($id);

        return view('scoring-result', [
            'score' => $score,
            'assessment' => $score->assessment
        ]);
    } catch (\Exception $e) {
        Log::error("Scoring result error", ['id' => $id, 'error' => $e->getMessage()]);
        abort(404);
    }
}
    

private function extractTextFromFile($file)
{
    $extension = $file->getClientOriginalExtension();

    switch ($extension) {
        case 'pdf':
            return $this->extractTextFromPDF($file);
        case 'docx':
            return $this->extractTextFromWord($file);
        case 'pptx':
            return $this->extractTextFromPPT($file);
        default:
            return null;
    }
}

private function extractTextFromPDF($file)
{
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($file->getPathname());
        $text = $pdf->getText();

        if (empty(trim($text))) {
            Log::warning("PDF text extraction failed. Trying OCR...");
            $text = $this->extractTextFromImage($file);
        }
        return $text;
    } catch (\Exception $e) {
        Log::error("PDF extraction error: " . $e->getMessage());
        return null;
    }
}

private function extractTextFromWord($file)
{
    try {
        $phpWord = IOFactory::load($file->getPathname());
        $text = "";

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                            $text .= $textElement->getText() . " ";
                        }
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $element->getText() . " ";
                }
            }
        }
        return trim($text);
    } catch (\Exception $e) {
        Log::error("Word extraction error: " . $e->getMessage());
        return null;
    }
}

private function extractTextFromPPT($file)
{
    try {
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === TRUE) {
            // Debug: Log if the file is successfully opened
            Log::info("PPTX file opened successfully.");

            $text = "";
            $slideIndex = 1;

            // Loop through the slides (ppt/slides/slide1.xml, ppt/slides/slide2.xml, ...)
            while ($zip->locateName('ppt/slides/slide' . $slideIndex . '.xml') !== false) {
                $slideContent = $zip->getFromName('ppt/slides/slide' . $slideIndex . '.xml');

                // Parse the XML content
                $xml = simplexml_load_string($slideContent);
                if ($xml === false) {
                    Log::error("Failed to parse XML for slide: " . $slideIndex);
                    continue;
                }

                // Extract the text from the XML
                foreach ($xml->xpath('//a:t') as $textElement) {
                    $text .= (string)$textElement . " ";  // Append extracted text
                }

                Log::info("Processed slide " . $slideIndex); // Log which slide is being processed
                $slideIndex++;
            }

            // If no text is extracted, fall back to OCR
            if (empty(trim($text))) {
                Log::warning("No text extracted from PPTX. Attempting OCR...");
                $text = $this->extractTextFromImage($file);
            }

            $zip->close();  // Close the ZIP archive
            return trim($text);
        } else {
            Log::error("Failed to open PPTX file as a ZIP.");
            return null;
        }
    } catch (\Exception $e) {
        Log::error("PPT extraction error: " . $e->getMessage());
        return null;
    }
}



private function extractTextFromImage($file)
{
    try {
        $zip = new \ZipArchive();
        if ($zip->open($file->getPathname()) === TRUE) {
            // Locate the media folder
            $mediaFolder = 'ppt/media/';
            $images = [];

            // Find all image files in the PPTX file
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                if (strpos($fileName, $mediaFolder) !== false && preg_match('/\.(jpg|jpeg|png|gif)$/i', $fileName)) {
                    // Save image file paths
                    $images[] = $zip->getFromName($fileName);
                }
            }

            // If no images were found, return a default error message
            if (count($images) === 0) {
                Log::error("No images found in PPTX file.");
                return "No images detected in the presentation.";
            }

            // Process the first image using Tesseract OCR
            $image = $images[0];  // Process the first image (can iterate over all if needed)

            // Write the image to a temporary file
            $tempImagePath = storage_path('app/temp_image.jpg');
            file_put_contents($tempImagePath, $image);

            // Run OCR on the image using Tesseract
            $ocr = new TesseractOCR($tempImagePath);
            $ocr->setBinPath("C:\\Program Files\\Tesseract-OCR\\tesseract.exe");
            $text = $ocr->run();

            // Clean up the temporary image file
            unlink($tempImagePath);

            return $text ?: "No text detected in the image.";
        } else {
            Log::error("Failed to open PPTX file as a ZIP.");
            return "Error processing PPTX file.";
        }
    } catch (\Exception $e) {
        Log::error("Tesseract OCR Error: " . $e->getMessage());
        return "OCR processing failed.";
    }
}

}
