<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpPresentation\IOFactory as PptIOFactory;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
class ObjectiveAssessmentController extends Controller
{
    public function generateAssessment(Request $request)
    {
    ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
    ini_set('memory_limit', '512M');    // optional, increase if needed
    set_time_limit(300);  

    $request->validate([
        'learning_material' => 'required|file|mimes:pdf,docx,pptx',
        'question_type' => 'required|string',
        'num_items' => 'required|integer|min:1|max:100',
        'num_options' => 'nullable|integer|min:2|max:10',
        'title' => 'nullable|string|max:255',
        'instruction' => 'nullable|string|max:1000',
        'bloom_taxonomy' => 'required|json',
    ]);

    $bloomTaxonomy = json_decode($request->input('bloom_taxonomy'), true);
    $file = $request->file('learning_material');
    $text = $this->extractTextFromFile($file);

    if (!$text || trim($text) === '') {
        return response()->json(['error' => '❌ Failed to extract text from file or file is empty.'], 400);
    }

    $questionType = $request->input('question_type');
    $numItems = $request->input('num_items');
    $numOptions = $request->input('num_options');
    $title = $request->input('title');
    $subject = $request->input('subject');
    $instruction = $request->input('instruction');

    $requiresRubric = in_array($questionType, [
        'Essay', 'Short Answer Questions', 'Critically Thought-out Opinions'
    ]);

    // Build the shared part of the prompt (without question count)
    $basePrompt = "You are an expert teacher. Based on the learning material below, generate exactly :count questions. ";
    $basePrompt .= "Follow this Bloom's Taxonomy distribution:\n";
    foreach ($bloomTaxonomy as $level => $percent) {
        $basePrompt .= ucfirst($level) . ": {$percent}%, ";
    }
    $basePrompt .= "\nUse appropriate verbs per level (e.g., remember → list, define; apply → solve, implement).\n";
    $basePrompt .= "Do NOT label each question by Bloom level, just follow the distribution in structure and style.\n\n";

    switch ($questionType) {
        case 'Multiple Choice':
            $basePrompt .= "multiple choice questions. Each should have $numOptions options labeled A to " . chr(64 + $numOptions) . " and one correct answer. Format like:\n1. Question?\nA) Option\n...\nAnswer: B, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'True Or False':
            $basePrompt .= "True or False questions. Format like:\n1. Statement?\nAnswer: True, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Fill In The Blanks':
            $basePrompt .= "fill in the blanks questions. Format like:\n1. This is a ___ question.\nAnswer: the missing word, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Identification':
            $basePrompt .= "identification questions. Format like:\n1. This is a question.\nAnswer: the correct term, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Enumeration':
            $basePrompt .= "enumeration questions. Format like:\n1. List 5 examples...\nAnswer: item 1, item 2, item 3, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Matching Type':
            $basePrompt .= " matching type questions. Format strictly like:\n1. [Matching term or phrase]\nAnswer: A: [Correct match for item 1]\n2. [Matching term or phrase]\nAnswer: B: [Correct match for item 2]\n\nDo not provide any list of options, rubric, or additional explanations. Only provide exactly one matching pair per item as shown.";
            break;            
        case 'Essay':
            $basePrompt .= "essay questions. provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. Describe a significant life experience that has shaped your perspective. 
              \n2. Is social media a net positive or negative for society?
              \nRubric:
              \nCriteria | Weight |	Excellent (100%) | Proficient (75%) | Basic (50%) |	Needs Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text 
              note: please don't include arterisk, dash hasgtags or etc.";
            break;
        case 'Short Answer Questions':
            $basePrompt .= "short answer questions. provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. What is the chemical symbol for water? 
              \n2. In what year did World War II begin? ?
              \nCriteria | Weight |	Excellent (100%) | Proficient (75%) | Basic (50%) |	Needs Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text 
              note: please don't include arterisk, dash or etc.";
            break;
        case 'Critically Thought-out Opinions':
            $basePrompt .= "critical thinking questions. provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. What problem or issue is being addressed?
              \n2. What evidence or data supports this claim or argument?
              \nCriteria | Weight |	Excellent (100%) | Proficient (75%) | Basic (50%) |	Needs Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text 
              note: please don't include arterisk, dash or etc.";
            break;
        default:
            $basePrompt .= "$questionType questions.  Format like: (\n1. Question .\nAnswer: Answer.) Please don't provide a rubric, just obey what i told you in the format.";
    }

    $basePrompt .= "\n\nDo not include instructions. Only output the questions and answers. If applicable, add the rubric at the end.
                    \n\nPlease generate the questions in the same language as the provided material. Do not change the language, whether it's Filipino, English, or any other language.\n\nMaterial:\n$text";

    $batchSize = 10; 
    $chunks = ceil($numItems / $batchSize);
    $allContent = "";

    for ($i = 0; $i < $chunks; $i++) {
        $count = ($i == $chunks - 1) ? $numItems - ($batchSize * $i) : $batchSize;

        $prompt = str_replace(':count', $count, $basePrompt);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)        
        ->connectTimeout(30)  
        ->post('https://api.openai.com/v1/chat/completions', [
            
            'model' => 'gpt-4-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.5,
            'max_tokens' => 2000,
        ]);

        if ($response->failed()) {
            Log::error("GPT error: " . $response->body());
            return response()->json(['error' => '⚠️ Failed to generate questions'], 500);
        }

        $content = $response->json()['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            return response()->json(['error' => '⚠️ No content generated'], 500);
        }

        $allContent .= "\n" . $content;
    }
    
    $rubric = null;
    if ($requiresRubric && preg_match('/(Criteria\s*\|\s*Weight\s*\|.+)/is', $allContent, $match)) {
        $rubric = trim($match[1]);
        $allContent = str_replace($match[0], '', $allContent);
    }    

    $assessment = Assessment::create([
        'teacher_id' => Auth::id(),
        'title' => $title,
        'subject' => $subject,
        'instructions' => $instruction,
        'question_type' => $questionType,
        'rubric' => $rubric,
    ]);

    $questions = preg_split('/\n(?=\d+\.\s)/', trim($allContent));
    $sequence = 1;

    foreach ($questions as $q) {
        $q = trim($q);
        if (!$q) continue;
    
        [$qText, $answerPart] = array_pad(explode("Answer:", $q, 2), 2, null);
        $questionText = trim($qText);
        $answerKey = trim($answerPart);
    
        $options = null;
    
        if ($questionType === 'multiplechoice') {
            preg_match_all('/[A-Z]\)\s*(.*?)\s*(?=[A-Z]\)|$)/s', $questionText, $matches);
            $options = !empty($matches[1]) ? $matches[1] : null;
        }
    
        if ($requiresRubric) {
            $answerKey = 'N/A';
        } elseif (empty($answerKey)) {
            $answerKey = 'N/A';
        }
    
        AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'question_text' => $questionText,
            'options' => $options ? json_encode($options) : null,
            'answer_key' => $answerKey,
            'sequence_number' => $sequence++,
        ]);
    }

    return response()->json([
        'redirect' => route('preview', ['id' => $assessment->id])
    ]);
}


public function preview()
{
    $assessment = Assessment::with('questions')
        ->where('teacher_id', Auth::id())
        ->latest()
        ->first();

    if (!$assessment) {
        return redirect()->route('home')->with('error', 'Assessment not found.');
    }
        $teacher = auth()->guard('web')->user(); // or use the correct guard
        $classes = $teacher->classes; // assuming you have a 'classes' relationship on Teacher model

    return view('AssessmentPreview', compact('assessment', 'classes'));
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
