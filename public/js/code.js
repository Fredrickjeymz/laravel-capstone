/*
    public function generateAssessment(Request $request)
{
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

    $prompt = "You are an expert teacher. Based on the learning material below, generate exactly $numItems ";
    $prompt .= "questions. Follow this Bloom's Taxonomy distribution:\n";
    foreach ($bloomTaxonomy as $level => $percent) {
        $prompt .= ucfirst($level) . ": {$percent}%, ";
    }
    $prompt .= "\nUse appropriate verbs per level (e.g., remember → list, define; apply → solve, implement).\n";
    $prompt .= "Do NOT label each question by Bloom level, just follow the distribution in structure and style.\n\n";

    switch ($questionType) {
        case 'Multiple Choice':
            $prompt .= "multiple choice questions. Each should have $numOptions options labeled A to " . chr(64 + $numOptions) . " and one correct answer. Format like:\n1. Question?\nA) Option\n...\nAnswer: B, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'True Or False':
            $prompt .= "True or False questions. Format like:\n1. Statement?\nAnswer: True, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Fill In The Blanks':
            $prompt .= "fill in the blanks questions. Format like:\n1. This is a ___ question.\nAnswer: the missing word, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Identification':
            $prompt .= "identification questions. Format like:\n1. This is a question.\nAnswer: the correct term, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Enumeration':
            $prompt .= "enumeration questions. Format like:\n1. List 5 examples...\nAnswer: item 1, item 2, item 3, Please don't provide a rubric, just obey what i told you in the format.";
            break;
        case 'Matching Type':
            $prompt .= " matching type questions. Format strictly like:\n1. [Matching term or phrase]\nAnswer: A: [Correct match for item 1]\n2. [Matching term or phrase]\nAnswer: B: [Correct match for item 2]\n\nDo not provide any list of options, rubric, or additional explanations. Only provide exactly one matching pair per item as shown.";
            break;            
        case 'Essay':
            $prompt .= "essay questions. provide a scoring rubric with the following:
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
            $prompt .= "short answer questions. provide a scoring rubric with the following:
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
            $prompt .= "critical thinking questions. provide a scoring rubric with the following:
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
            $prompt .= "$questionType questions.  Format like: (\n1. Question .\nAnswer: Answer.) Please don't provide a rubric, just obey what i told you in the format.";
    }

    $prompt .= "\n\nDo not include instructions. Only output the questions and answers. If applicable, add the rubric at the end.
                \n\nPlease generate the questions in the same language as the provided material. Do not change the language, whether it's Filipino, English, or any other language.\n\nMaterial:\n$text";

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
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

    $rubric = null;
    if ($requiresRubric && preg_match('/(Criteria\s*\|\s*Weight\s*\|.+)/is', $content, $match)) {
        $rubric = trim($match[1]);
        $content = str_replace($match[0], '', $content);
    }    

    $assessment = Assessment::create([
        'teacher_id' => Auth::id(),
        'title' => $title,
        'subject' => $subject,
        'instructions' => $instruction,
        'question_type' => $questionType,
        'rubric' => $rubric,
    ]);

    $questions = preg_split('/\n(?=\d+\.\s)/', trim($content));
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
*/