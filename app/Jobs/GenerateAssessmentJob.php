<?php

// app/Jobs/GenerateAssessmentJob.php

namespace App\Jobs;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateAssessmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $assessmentId;
    public $fileText;
    public $payload;
    public $teacherId;
    public $tries;
    public $timeout;

    public function __construct(int $assessmentId, string $fileText, array $payload, int $teacherId)
    {
        $this->assessmentId = $assessmentId;
        $this->fileText = $fileText;
        $this->payload = $payload;
        $this->teacherId = $teacherId;
        $this->tries = 3;
        $this->timeout = 600;
    }

    public function handle()
    {
        Log::info('🎯 GenerateAssessmentJob STARTED', ['assessment_id' => $this->assessmentId]);
        
        $assessment = Assessment::find($this->assessmentId);
        if (!$assessment) {
            Log::error("❌ Assessment not found", ['assessment_id' => $this->assessmentId]);
            return;
        }

        // Mark in-progress immediately
        $assessment->update(['status' => 'in-progress']);
        Log::info('📊 Assessment status updated to in-progress');

        $bloomTaxonomy = json_decode($this->payload['bloom_taxonomy'], true);
        $questionType = $this->payload['question_type'];
        $numItems = (int) $this->payload['num_items'];
        $numOptions = $this->payload['num_options'] ?? null;

        $buildBasePrompt = function(int $count) use ($bloomTaxonomy, $questionType, $numOptions) {
            $basePrompt = "You are an expert teacher. Based on the learning material below, generate exactly :count questions. ";
            $basePrompt .= "Follow this Bloom's Taxonomy distribution:\n";
            foreach ($bloomTaxonomy as $level => $percent) {
                $basePrompt .= ucfirst($level) . ": {$percent}%, ";
            }
            $basePrompt .= "\nUse appropriate verbs per level (e.g., remember → list, define; apply → solve, implement).\n";
            $basePrompt .= "Do NOT label each question by Bloom level, just follow the distribution in structure and style.\n\n";

            switch ($questionType) {
                case 'Multiple Choice':
                    $maxOpt = max(2, min((int)$numOptions ?: 4, 10));
                    $basePrompt .= "multiple choice questions. Each should have {$maxOpt} options labeled A to " . chr(64 + $maxOpt) . " and one correct answer. Format like:\n1. Question?\nA) Option\n...\nAnswer: B";
                    break;
                case 'True Or False':
                    $basePrompt .= "True or False questions. Format like:\n1. Statement?\nAnswer: True";
                    break;
                case 'Fill In The Blanks':
                    $basePrompt .= "fill in the blanks questions. Format like:\n1. This is a ___ question.\nAnswer: the missing word";
                    break;
                case 'Identification':
                    $basePrompt .= "identification questions. Format like:\n1. This is a question.\nAnswer: the correct term";
                    break;
                case 'Enumeration':
                    $basePrompt .= "enumeration questions. Format like:\n1. List 5 examples...\nAnswer: item 1, item 2, item 3";
                    break;
                case 'Matching Type':
                    $basePrompt .= "matching type questions. Format strictly like:\n1. [Matching term or phrase]\nAnswer: A: [Correct match for item 1]\n2. [Matching term or phrase]\nAnswer: B: [Correct match for item 2]\n\nDo not provide any list of options, rubric, or additional explanations. Only provide exactly one matching pair per item as shown.";
                    break;
                case 'Essay':
                    $basePrompt .= "essay questions. Provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. Describe a significant life experience that has shaped your perspective. 
              \n2. Is social media a net positive or negative for society?
              \nRubric:
              \nCriteria | Weight |\tExcellent (100%) | Proficient (75%) | Basic (50%) |\tNeeds Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text";
                    break;
                case 'Short Answer Questions':
                    $basePrompt .= "short answer questions. Provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. What is the chemical symbol for water? 
              \n2. In what year did World War II begin?
              \nCriteria | Weight |\tExcellent (100%) | Proficient (75%) | Basic (50%) | \tNeeds Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text";
                    break;
                case 'Critically Thought-out Opinions':
                    $basePrompt .= "critical thinking questions. Provide a scoring rubric with the following:
            - 5 criteria such as content and development, organization, grammar & mechanics, critical thinking, and clarity & coherence.
            - Each criterion should have a description and percentage weight.
              Format like:\n1. What problem or issue is being addressed?
              \n2. What evidence or data supports this claim or argument?
              \nCriteria | Weight |\tExcellent (100%) | Proficient (75%) | Basic (50%) | \tNeeds Improvement (25%)
              \nContent & Development | 30% | Demonstrates deep understanding; insightful, original ideas; strong supporting evidence | text | text | text
              \nOrganization | 20% | text | text | text | text 
              \nGrammar and Mechanics | 20% | text | text | text | text 
              \nCritical Thinking | 20% | text | text | text | text 
              \nClarity & Coherence | 10% | | text | text | text | text";
                    break;
                default:
                    $basePrompt .= "{$questionType} questions. Format like:\n1. Question.\nAnswer: Answer.";
            }

            $basePrompt .= "\n\nIMPORTANT FORMATTING RULES:\n";
            $basePrompt .= "1. Each question must start with a number and period (like '1. ')\n";
            $basePrompt .= "2. Each answer must start with 'Answer: ' followed by the answer\n";
            $basePrompt .= "3. Do not include any other text, instructions, or explanations\n";
            $basePrompt .= "4. Generate exactly :count questions\n";
            $basePrompt .= "5. Please generate the questions in the same language as the provided material. Do not change the language, whether it's Filipino, English, or any other language.\n\n";
            
            $basePrompt .= "Material:\n" . $this->fileText;
            $basePrompt = str_replace(':count', (string)$count, $basePrompt);

            return $basePrompt;
        };

        // Batch settings
        $batchSize = min(5, $numItems); // Ensure batch size doesn't exceed total items
        $chunks = (int) ceil($numItems / $batchSize);
        $sequence = 1;
        $totalQuestionsCreated = 0;
        $rubricCaptured = null;

        Log::info('Starting batch processing', [
            'total_items' => $numItems,
            'batch_size' => $batchSize,
            'total_chunks' => $chunks
        ]);

        for ($i = 0; $i < $chunks; $i++) {
            $remaining = $numItems - ($i * $batchSize);
            $count = min($batchSize, $remaining); // Ensure we don't exceed remaining items

            if ($count <= 0) {
                Log::info('No more items to process, stopping');
                break;
            }

            Log::info("🔄 Processing batch {$i}", [
                'questions_in_batch' => $count,
                'sequence_start' => $sequence
            ]);

            $prompt = $buildBasePrompt($count);

            $responseContent = null;
            $attempts = 0;
            $maxAttempts = 3;

            while ($attempts < $maxAttempts && !$responseContent) {
                $attempts++;
                try {
                    Log::info("📡 OpenAI API call attempt {$attempts} for batch {$i}");
                    
                    $resp = Http::withHeaders([
                        'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                        'Content-Type' => 'application/json',
                    ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are an expert teacher who follows formatting rules exactly.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.3,
                        'max_tokens' => 4000,
                    ]);

                    if ($resp->successful()) {
                        $responseContent = $resp->json('choices.0.message.content');
                        Log::info("✅ OpenAI response received for batch {$i}", [
                            'response_length' => strlen($responseContent),
                            'preview' => substr($responseContent, 0, 200) . '...'
                        ]);
                    } else {
                        Log::warning("⚠️ OpenAI API failed", [
                            'status' => $resp->status(),
                            'response' => $resp->body()
                        ]);
                        sleep(2 * $attempts);
                    }
                } catch (\Throwable $e) {
                    Log::error("❌ OpenAI exception", [
                        'attempt' => $attempts,
                        'error' => $e->getMessage()
                    ]);
                    sleep(1 + $attempts);
                }
            }

            if (!$responseContent) {
                Log::error("🚫 Failed to get OpenAI response for batch {$i} after {$maxAttempts} attempts");
                continue;
            }

            // Parse questions from response
            $questions = $this->parseQuestionsFromResponse($responseContent, $questionType);
            Log::info("📝 Parsed questions from batch {$i}", [
                'questions_found' => count($questions),
                'questions_sample' => array_slice($questions, 0, 2) // Log first 2 questions only
            ]);

            $batchQuestionsCreated = 0;
            foreach ($questions as $questionData) {
                if ($sequence > $numItems) {
                    Log::info("🎯 Reached requested number of items ({$numItems}), stopping");
                    break 2; // Break out of both loops
                }

                try {
                    AssessmentQuestion::create([
                        'assessment_id' => $assessment->id,
                        'question_text' => $questionData['question_text'],
                        'options' => $questionData['options'],
                        'answer_key' => $questionData['answer_key'],
                        'sequence_number' => $sequence++,
                    ]);
                    $batchQuestionsCreated++;
                    $totalQuestionsCreated++;
                    
                    Log::debug("✅ Saved question", [
                        'sequence' => $sequence - 1,
                        'question_preview' => substr($questionData['question_text'], 0, 50)
                    ]);
                } catch (\Throwable $e) {
                    Log::error("❌ Failed to save question", [
                        'sequence' => $sequence,
                        'error' => $e->getMessage(),
                        'question_data' => $questionData
                    ]);
                }
            }

            Log::info("✅ Batch {$i} completed", [
                'questions_created' => $batchQuestionsCreated,
                'total_questions_created' => $totalQuestionsCreated
            ]);

            // Small delay between batches to prevent rate limiting
            if ($i < $chunks - 1) {
                sleep(1);
            }
        }

        // Update status based on actual results
        $finalQuestionsCount = $assessment->questions()->count();
        $finalStatus = $finalQuestionsCount > 0 ? 'completed' : 'failed';
        
        $assessment->update(['status' => $finalStatus]);
        
        Log::info('🎉 GenerateAssessmentJob COMPLETED', [
            'assessment_id' => $this->assessmentId,
            'total_questions_created' => $finalQuestionsCount,
            'final_status' => $finalStatus
        ]);
    }

    /**
     * Improved question parsing method
     */
    private function parseQuestionsFromResponse(string $responseContent, string $questionType): array
    {
        $questions = [];
        
        // Normalize line endings and remove extra spaces
        $content = preg_replace('/\r\n/', "\n", $responseContent);
        $content = preg_replace('/\n+/', "\n", $content);
        
        // Split by questions - look for lines starting with numbers
        $lines = explode("\n", trim($content));
        $currentQuestion = '';
        $currentAnswer = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Check if this line starts a new question (number followed by period)
            if (preg_match('/^\d+\.\s*(.+)/', $line, $matches)) {
                // Save previous question if exists
                if (!empty($currentQuestion)) {
                    $questions[] = [
                        'question_text' => trim($currentQuestion),
                        'answer_key' => trim($currentAnswer) ?: 'N/A',
                        'options' => $this->extractOptions($currentQuestion, $questionType)
                    ];
                }
                
                // Start new question
                $currentQuestion = $matches[1];
                $currentAnswer = '';
            }
            // Check if this line contains the answer
            elseif (preg_match('/^Answer:\s*(.+)/i', $line, $matches)) {
                $currentAnswer = $matches[1];
            }
            // If it's part of the current question (not an answer line)
            elseif (empty($currentAnswer) && !preg_match('/^Answer:/i', $line)) {
                $currentQuestion .= " " . $line;
            }
        }
        
        // Don't forget the last question
        if (!empty($currentQuestion)) {
            $questions[] = [
                'question_text' => trim($currentQuestion),
                'answer_key' => trim($currentAnswer) ?: 'N/A',
                'options' => $this->extractOptions($currentQuestion, $questionType)
            ];
        }
        
        return $questions;
    }

    /**
     * Extract options for multiple choice questions
     */
    private function extractOptions(string $questionText, string $questionType): ?string
    {
        if (strtolower($questionType) !== 'multiple choice') {
            return null;
        }
        
        $options = [];
        
        // Look for options like A) Option, B) Option, etc.
        preg_match_all('/([A-Z])[\)\.]\s*([^\n]+)/', $questionText, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $letter) {
                $options[] = trim($matches[2][$index]);
            }
        }
        
        return !empty($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : null;
    }

    public function failed(\Throwable $exception)
    {
        Log::error('💥 GenerateAssessmentJob FAILED', [
            'assessment_id' => $this->assessmentId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $assessment = Assessment::find($this->assessmentId);
        if ($assessment) {
            $assessment->update(['status' => 'failed']);
        }
    }
}