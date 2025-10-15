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
use App\Helpers\ActivityLogger;
use App\Jobs\GenerateAssessmentJob;
class ObjectiveAssessmentController extends Controller
{

    public function generateAssessment(Request $request)
    {
        try {
            $teacher = auth()->guard('web')->user();

            $request->validate([
                'learning_material' => 'required|file|mimes:pdf,docx,pptx',
                'question_type' => 'required|string',
                'num_items' => 'required|integer|min:1|max:1000',
                'num_options' => 'nullable|integer|min:2|max:10',
                'title' => 'nullable|string|max:255',
                'instruction' => 'nullable|string|max:1000',
                'subject' => 'nullable|string|max:255',
                'bloom_taxonomy' => 'required|json',
            ]);

            $bloomTaxonomy = json_decode($request->input('bloom_taxonomy'), true);
            $file = $request->file('learning_material');
            $text = $this->extractTextFromFile($file);

            if (!$text || trim($text) === '') {
                return response()->json([
                    'success' => false,
                    'error' => '❌ Failed to extract text from file or file is empty.'
                ], 400);
            }

            $payload = [
                'bloom_taxonomy' => $request->input('bloom_taxonomy'),
                'question_type' => $request->input('question_type'),
                'num_items' => (int) $request->input('num_items'),
                'num_options' => $request->input('num_options'),
                'title' => $request->input('title'),
                'subject' => $request->input('subject'),
                'instruction' => $request->input('instruction'),
            ];

            /*$standardRubric = "Criteria | Weight | Excellent (100%) | Proficient (75%) | Basic (50%) | Needs Improvement (25%)
            Content & Development | 30% | Demonstrates deep understanding; provides insightful, original ideas; all points are fully supported with evidence | Shows clear understanding; ideas mostly well-developed and supported; minor gaps in evidence | Shows some understanding; ideas partially developed; limited evidence | Demonstrates minimal understanding; ideas undeveloped or unsupported
            Organization | 20% | Information is logically and coherently organized; clear introduction, body, and conclusion | Information mostly organized; minor lapses in clarity or structure | Organization is inconsistent; transitions unclear | Information is disorganized; lacks clear structure
            Grammar and Mechanics | 20% | Virtually no errors in spelling, punctuation, or grammar | Few minor errors that do not hinder comprehension | Several errors that sometimes interfere with understanding | Frequent errors that significantly hinder comprehension
            Critical Thinking | 20% | Analysis is thorough, insightful, and demonstrates strong reasoning; evaluates multiple perspectives | Analysis is clear and logical; considers some perspectives | Analysis is limited; reasoning is sometimes unclear | Little to no analysis; reasoning is weak or absent
            Clarity & Coherence | 10% | Writing is clear, concise, and easy to follow; excellent flow | Writing is mostly clear; minor issues with flow or clarity | Writing sometimes unclear; ideas occasionally hard to follow | Writing is unclear; ideas difficult to follow or confusing";
            */

            $assessment = Assessment::create([
                'teacher_id' => Auth::id(),
                'title' => $payload['title'] ?? 'Untitled Assessment',
                'subject' => $payload['subject'] ?? null,
                'instructions' => $payload['instruction'] ?? null,
                'question_type' => $payload['question_type'],
                'rubric' => null,
                'status' => 'pending',
            ]);

            $teacher = $assessment->teacher;
            $creatorName = "{$teacher->fname} {$teacher->mname} {$teacher->lname}";
            ActivityLogger::log(
                "Generated Assessment",
                "Assessment Title: {$assessment->title}, Created by: {$creatorName}"
            );

            // 🔥 Try dispatching the job, catch errors
            try {
                GenerateAssessmentJob::dispatch($assessment->id, $text, $payload, Auth::id());
            } catch (\Throwable $e) {
                Log::error("❌ Failed to dispatch GenerateAssessmentJob: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error' => 'Job dispatch failed — please check your queue configuration (Redis/Worker).',
                    'details' => $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'redirect' => route('preview', ['id' => $assessment->id]), // ✅ include the ID
                'assessment_id' => $assessment->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation failed: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'details' => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            Log::error("Unexpected error in generateAssessment(): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Unexpected server error.',
                'details' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function preview($id = null)
    {
        // If no ID provided, get the latest assessment (your existing logic)
        if (!$id) {
            $assessment = Assessment::with('questions')
                ->where('teacher_id', Auth::id())
                ->latest()
                ->first();
        } else {
            // Get the specific assessment by ID
            $assessment = Assessment::with('questions')
                ->where('id', $id)
                ->where('teacher_id', Auth::id())
                ->firstOrFail();
        }

        if (!$assessment) {
            return redirect()->route('home')->with('error', 'Assessment not found.');
        }

        $teacher = auth()->guard('web')->user();
        $classes = $teacher->classes;

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
