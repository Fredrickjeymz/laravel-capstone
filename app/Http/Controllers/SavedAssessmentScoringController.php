<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedAssessment;
use App\Models\SavedAssessmentQuestion;
use App\Models\SavedStudentAssessmentScore;
use App\Models\SavedStudentAssessmentQuestionScore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory as PptIOFactory;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;


class SavedAssessmentScoringController extends Controller
{
    public function evaluateAnswers(Request $request)
{
    Log::info('Starting evaluateAnswers', ['request' => $request->except('saved_answer_file')]);

    $request->validate([
        'saved_assessment_id' => 'required|exists:saved_assessments,id', // Changed to match model
        'saved_student_name' => 'required|string|max:255',
        'saved_answer_file' => 'required|file|mimes:pdf,docx,txt'
    ]);

    try {
        $file = $request->file('saved_answer_file');
        $studentAnswers = $this->extractTextFromFile($file);

        if (empty(trim($studentAnswers))) {
            throw new \Exception("Answer file is empty or unreadable.");
        }

        $assessment = SavedAssessment::with('questions')->findOrFail($request->saved_assessment_id);

        $evaluationData = [
            'assessment' => $assessment,
            'student_answers' => $studentAnswers,
            'student_name' => $request->saved_student_name,
            'question_type' => $assessment->question_type
        ];

        $aiResponse = $this->callEvaluationAI($evaluationData);

        if (!isset($aiResponse['total_score'], $aiResponse['max_score'], $aiResponse['percentage'], $aiResponse['question_results']) || !is_array($aiResponse['question_results'])) {
            throw new \Exception("Incomplete or invalid AI response.");
        }

        $filePath = $file->store('student_answers');

        // Save overall score
        $score = SavedStudentAssessmentScore::create([
            'saved_assessment_id' => $assessment->id,
            'student_name' => $request->saved_student_name,
            'student_file_path' => $filePath,
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

            SavedStudentAssessmentQuestionScore::create([
                'saved_student_assessment_score_id' => $score->id,
                'saved_assessment_question_id' => $questions[$index]->id,
                'student_answer' => $result['student_answer'] ?? '',
                'score_given' => $result['score'] ?? 0,
                'max_score' => $result['max_score'] ?? 1,
                'criteria_scores' => isset($result['criteria_scores']) ? json_encode($result['criteria_scores']) : null,
            ]);
        }


        return response()->json(['redirect' => route('saved-scoring-result', $score->id)]);

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
    Log::info('Building AI evaluation prompt');

    $prompt = "Evaluate student answers against the following assessment:\n\n";
    $prompt .= "ASSESSMENT TITLE: {$data['assessment']->title}\n";
    $prompt .= "QUESTION TYPE: {$data['question_type']}\n\n";

    foreach ($data['assessment']->questions as $q) {
        $prompt .= "QUESTION {$q->sequence_number}:\n{$q->question_text}\n";
        $prompt .= "CORRECT ANSWER: {$q->answer_key}\n\n";
    }

    if ($data['assessment']->rubric) {
        $prompt .= "RUBRIC:\n{$data['assessment']->rubric}\n\n";
    }

    $prompt .= "STUDENT ANSWERS:\n{$data['student_answers']}\n\n";
    $prompt .= "INSTRUCTIONS:\n";
    $prompt .= "- Return JSON with total_score, max_score, percentage, and question_results.\n";
    $prompt .= "- Each question_results[] must have question_id, student_answer, score, max_score, feedback.\n";
    $prompt .= "- For subjective: include rubric-based criteria_scores.\n";
    $prompt .= "- JSON only. No extra explanation. Do NOT include markdown like ```json.\n\n";
    $prompt .= "JSON ONLY OUTPUT:";

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a strict but fair teacher evaluating student work.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            throw new \Exception("OpenAI API failed with status " . $response->status());
        }

        $content = $response->json('choices.0.message.content');

        if (!$content) {
            throw new \Exception("AI returned empty content.");
        }

        // Clean the content
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*([\s\S]*?)\s*```$/', '$1', $content);
        $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content); // remove control characters

        Log::info('Sanitized AI content', ['content' => $content]);

        $result = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON parsing failed', [
                'error' => json_last_error_msg(),
                'content' => $content
            ]);
            throw new \Exception("Invalid JSON: " . json_last_error_msg());
        }

        return $result;

    } catch (\Exception $e) {
        Log::error('AI call failed', ['error' => $e->getMessage()]);
        throw $e;
    }
}


public function scoringResult($id)
{
    try {
        $score = SavedStudentAssessmentScore::with([
            'questionScores.savedAssessmentQuestion',
            'savedAssessment.questions'
        ])->findOrFail($id);

        return view('saved-scoring-result', [
            'score' => $score,
            'assessment' => $score->savedAssessment // ✅ Corrected relationship
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

public function viewSavedResult($id)
{
    $score = SavedStudentAssessmentScore::with([
        'questionScores',
        'SavedAssessment.questions',
    ])->findOrFail($id);

    return view('saved-scoring-result-preview', [
        'score' => $score,
        'assessment' => $score->SavedAssessment
    ]);
}

}


