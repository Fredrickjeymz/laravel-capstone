@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <br>
    <button id="btn-return" class="btn-return" data-url="{{ route('assigned-ass') }}">
        Return
    </button>
    <div class="top">
        <h2>Item Analysis</h2>
        <p>Item Analysis for <strong>{{ $assessment->title }}</strong></p>
    </div>

    <div class="excel-table-container">
        <div class="analysis-main-header">
            <div class="logo-one">
                <img src="{{ asset('image/division_logo.png') }}" class="division-logo" alt="division logo">
            </div>
            <div class="analysis-header">
                <p>Republic of the Philippines</p>
                <p>Department of Education</p>
                <p>Region I - Ilocos Region</p>
                <p>School Divisions of San Carlos City</p>
                <p><strong>Speaker Eugenio Perez National Agricultural School</strong></p>
                <p><strong>San Carlos City, Pangasinan</strong></p>
                <p><strong>ITEM ANALYSIS -</strong> <span style="text-transform: uppercase">{{ $class->year_level }} {{ $class->subject }}</span></p>
            </div> 
            <div class="logo-two">
                <img src="{{ asset('image/sepnas_logo.png') }}" class="sepnas-logo" alt="school logo">
            </div>
        </div>
        <table class="excel-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Correct</th>
                    <th>Incorrect</th>
                    <th>Percentage</th>
                    <th>Difficulty</th>
                    <th>Mastery level</th>
                    <th>Possible Causes for errorneous answers</th>
                </tr>
            </thead>

            <tbody>
                @php $totalCorrect = 0; @endphp

                @foreach ($itemAnalysis as $index => $item)
                    @php $totalCorrect += $item['correct']; @endphp

                    <!-- Main Row -->
                    <tr class="question-row">
                        <td>{{ $index + 1 }}</td>
                        <td id="q-td">{{ $item['question'] }}</td>
                        <td>{{ $item['correct'] }}</td>
                        <td>{{ $item['incorrect'] }}</td>
                        <td>{{ $item['mps'] ?? $item['percentage'] }}%</td>
                        <td>{{ $item['difficulty'] }}</td>
                        <td>{{ $item['mastery_level'] }}</td>
                        <td>{{ $item['cause'] }}</td>
                    </tr>
                @endforeach

                <!-- Excel-style TOTAL ROW -->
                <tr class="total-row">
                    <td></td>
                    <td><strong>Total</strong></td>
                    <td><strong>{{ $totalCorrect }}</strong></td>
                    <td colspan="5"></td>
                </tr>

                <!-- Number of Examinees -->
                <tr class="summary-row">
                    <td></td>
                    <td><strong>Number of Examinees</strong></td>
                    <td><strong>{{ $itemAnalysis[0]['number_of_examinees'] ?? 0 }}</strong></td>
                    <td colspan="5"></td>
                </tr>

                <!-- Mean -->
                <tr class="summary-row">
                    <td></td>
                    <td><strong>MEAN</strong></td>
                    <td>
                        <strong>
                            {{ $mean }}
                        </strong>
                    </td>
                    <td colspan="5"></td>
                </tr>

                <!-- MPS -->
                <tr class="summary-row">
                    <td></td>
                    <td><strong>MPS</strong></td>
                    <td>
                        <strong>
                            {{ number_format($mps, 2) }}
                        </strong>
                    </td>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
        <div class="overall-feedback-box">
            <h3>Overall Assessment Feedback</h3>
            <p>{{ $overallFeedback }}</p>
        </div>
        <p class="p-by"><strong>Prepared by:</strong> {{ $itemAnalysis[0]['teachername'] ?? '' }} ({{ $itemAnalysis[0]['teacherposition'] ?? '' }})</p>
    </div>
</div>
@endsection