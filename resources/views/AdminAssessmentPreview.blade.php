@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
<div class="preview-area">
    <button id="admin-btn-return" class="btn-return" data-url="{{ route('generated') }}">
        Return
    </button>
    <div class="top">
        <h2>Assessment Preview</h2>
        <p>Generated Assesssment Preview.</p>
    </div>
        <div class="generated-area">
            <div class="gen-del" data-id="{{ $assessment->id }}">
            <div class="mb-6">
                <center>
                <div class="q-t">
                    @if ($assessment->title)
                        <p class="text-sm text-gray-600">{{ $assessment->title }}<p>
                    @endif
                </div>
                <div class="q-s">
                    @if ($assessment->subject)
                        <p class="text-sm text-gray-600">{{ $assessment->subject }}<p>
                    @endif
                </div>
                </center>

                @if ($assessment->instructions)
                @php
                    $questionTypeLabels = [
                        'TrueOrFalse' => 'True or False',
                        'multiplechoice' => 'Multiple Choice',
                        'FillInTheBlanks' => 'Fill in the Blanks',
                        'Identification' => 'Identification',
                        'Enumeration' => 'Enumeration',
                        'Matchingtype' => 'Matching Type',
                        'Essay' => 'Essay',
                        'Short Answer Questions' => 'Short Answer Questions',
                        'Critically Thought-out Opinions' => 'Critically Thought-out Opinions',
                    ];
                @endphp
                    <p class="text-sm text-gray-600">
                        @if ($assessment->question_type)
                            <span class="q-i"> {{ $questionTypeLabels[$assessment->question_type] ?? $assessment->question_type }}: </span>
                        @endif
                    {{ $assessment->instructions }}</p>
                @endif
            </div>

            {{-- Questions --}}
            <div class="q-l">
                <ol class="question-list">
                @foreach ($assessment->questions as $index => $question)
                    <li>
                        @php
                            $cleaned_text = preg_replace('/^\d+[\.\)]\s*/', '', $question->question_text);
                            $question_text = preg_split('/\s*[A-Z]\)[\s]*/', $cleaned_text)[0];
                            preg_match_all('/\s*([A-Z])\)[\s]*(.*?)(?=\s*[A-Z]\)|$)/', $question->question_text, $matches);
                        @endphp
                        <p>{{ trim($question_text) }}</p>
                        <p>
                            @foreach ($matches[1] as $key => $option_letter)
                                <p>{{ $option_letter }}) {{ trim($matches[2][$key]) }}</p>
                            @endforeach
                        </p>
                    </li>
                @endforeach
                </ol>

                {{-- Matching Type Options --}}
                @if ($assessment->question_type === 'Matching Type')
                    @php
                        $matchingOptions = $assessment->questions->pluck('answer_key')->toArray();
                        shuffle($matchingOptions);
                    @endphp
                    <div class="mt-6">
                        <h4 class="font-semibold">Options</h4>
                            @foreach ($matchingOptions as $i => $option)
                                <p>{{ $option }}</p>
                            @endforeach
                    </div>
                @endif
            </div>

            {{-- Answer Key (for objective types only) --}}
            @if (!in_array($assessment->question_type, ['Essay', 'Short Answer Questions', 'Critically Thought-out Opinions']))
                <div class="mt-10 p-4 bg-green-50 border-l-4 border-green-400">
                    <p class="a-k">Answer Key</p>

                    @foreach ($assessment->questions as $index => $question)
                        <div class="mb-4">
                            @if ($assessment->question_type === 'Enumeration')
                                @php
                                    $items = preg_split('/\s*,\s*/', $question->answer_key);
                                @endphp
                                <ul class="list-disc list-inside ml-6 text-green-800">
                                    <p>{{ $index + 1 }}. Answers:
                                    @foreach ($items as $item)
                                        <p><li><span class="cap">{{ $item }}</span></li></p>
                                    @endforeach
                                    </p>
                                </ul>
                            @elseif ($assessment->question_type === 'Matching Type')
                                <p>
                                    {{ $index + 1 }}. {{ $question->answer_key }}
                                </p>
                            @else
                                <p class="text-green-800 ml-6"><span>{{ $index + 1 }}. </span><span class="cap">{{ $question->answer_key }}</span></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Rubric (for subjective types only) --}}
            @if ($assessment->rubric)
                <div class="rubric-container">
                    <h3 class="rubric-title">Scoring Rubric</h3>

                    @php
                        $rows = preg_split("/\r\n|\n|\r/", trim($assessment->rubric));
                        $table = [];

                        foreach ($rows as $row) {
                            $cleaned = trim($row);
                            if ($cleaned === '' || preg_match('/^[-| ]+$/', $cleaned)) {
                                continue;
                            }
                            $table[] = array_map('trim', explode('|', $cleaned));
                        }
                    @endphp

                    @if (count($table) > 1)
                        <div class="rubric-table-wrapper">
                            <table class="rubric-table">
                                <thead>
                                    <tr>
                                        @foreach ($table[0] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_slice($table, 1) as $row)
                                        <tr>
                                            @foreach ($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rubric-text">{{ $assessment->rubric }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection