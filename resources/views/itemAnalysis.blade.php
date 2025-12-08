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
    <script>
        document.getElementById('btn-download-analysis').addEventListener('click', function() {
        // Get the table container
        const tableContainer = document.querySelector('.excel-table-container');
        
        // Create HTML with adjusted styles for Excel
        const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                }
                .analysis-main-header{
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;
                }
                .sepnas-logo{
                    width: 100px;
                    height: 100px;
                    object-fit: contain;
                }
                .division-logo{
                    width: 100px;
                    height: 100px;
                    object-fit: contain;
                }
                .analysis-header {
                    font-size: 14px;
                    text-align: center;
                    flex-grow: 1;
                    margin: 0 20px;
                }
                .analysis-header p{
                    margin: 3px 0;
                }
                .excel-table {
                    width: 100%;
                    background-color: #ffffff;
                    margin: 20px 0;
                    font-size: 12px;
                    border: 1px solid #b7d6b8;
                    border-collapse: collapse;
                }
                .excel-table thead {
                    background-color: #ccf8cc;
                    color: #4d4d4d;
                }
                .excel-table th,
                .excel-table td {
                    padding: 6px 8px;
                    border: 1px solid #cfe9d1;
                    text-align: left;
                }
                .excel-table th {
                    font-weight: 600;
                    color: #3e513f;
                }
                .excel-table tbody tr:nth-child(even) {
                    background-color: #edf8ee;
                }
                #q-td{
                    max-width: 250px;
                    word-wrap: break-word;
                }
                .excel-table-container {
                    width: 100%;
                    background-color: #ffffff;
                    padding: 20px;
                }
                .p-by{
                    font-size: 14px;
                    margin-top: 30px;
                    margin-bottom: 10px;
                }
                .overall-feedback-box {
                    background: #e9f7ef;
                    border-left: 6px solid #27ae60;
                    padding: 15px 20px;
                    margin-top: 25px;
                    border-radius: 6px;
                    font-size: 14px;
                }
                .overall-feedback-box h3 {
                    margin: 0 0 10px;
                    font-weight: bold;
                    color: #1e824c;
                }
                .total-row, .summary-row {
                    background-color: #e9f7ef !important;
                    font-weight: bold;
                }
                .logo-one, .logo-two {
                    width: 120px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            ${tableContainer.innerHTML.replace(/<img[^>]*>/g, '')}
        </body>
        </html>
        `;
        
        // Create and download the file
        const blob = new Blob([htmlContent], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        
        // Get subject name for filename
        const subjectSpan = document.querySelector('.analysis-header span');
        const subjectName = subjectSpan ? subjectSpan.textContent.trim() : 'ItemAnalysis';
        const fileName = `Item_Analysis_${subjectName.replace(/\s+/g, '_')}.xls`;
        
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
    </script>
    <div class="excel-table-container">
        <div class="dl-con">
            <button id="btn-download-analysis" class="download-analysis">Download Analysis</button>
        </div>
        <div class="analysis-main-header">
            <div class="logo-one">
                <img src="{{ asset('image/DEPED.png') }}" class="division-logo" alt="division logo">
            </div>
            <div class="analysis-header">
                <p class="big-lil"><strong>Republic of the Philippines</strong></p>
                <p class="big-t"><strong>Department of Education</strong></p>
                <p>Region I</p>
                <p>School Divisions of San Carlos City</p>
                <p><strong>SPEAKER AUGENIO PEREZ NATIONAL AGRICULTURAL SCHOOL</strong></p>
                <p>Roxas Boulevard, San Carlos City, Pangasinan</p>
                <div class="lineee"></div>
                <p class="biggest-t"><strong>ITEM ANALYSIS - <span style="text-transform: uppercase">{{ $class->year_level }} {{ $class->subject }}</span></strong></p>
                <p>School Year: 2025 - 2026</p>
            </div> 
            <div class="logo-two">
                <img src="{{ asset('image/sepnas_logo.png') }}" class="sepnas-logo" alt="school logo">
            </div>
        </div>
        <div class="sub-header">
            <p style="text-transform: uppercase"><strong>QUARTER: {{ $assessment->quarter ?? '' }}</strong></p>
            <p style="text-transform: uppercase"><strong>CLASS/SECTION: {{ $class->class_name ?? '' }}</strong></p>
        </div>
        <table class="excel-table">
            <thead>
                <tr>
                    <th>ITEM NO.</th>
                    <th>TOPIC</th>
                    <th>COMPETENCY</th>
                    <th>No. of LEARNERS w/ CORRECT ANSWERS</th>
                    <th>Percentage</th>
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
                        <td class="centered">{{ $index + 1 }}</td>
                        
                        {{-- Show topic/competency ONLY in first row, with rowspan --}}
                        @if($loop->first)
                            <td class="to-com" rowspan="{{ count($itemAnalysis) }}">{{ $assessment->topic ?? '' }}</td>
                            <td class="to-com" rowspan="{{ count($itemAnalysis) }}">{{ $assessment->competency ?? '' }}</td>
                        @endif
                        
                        <td class="centered">{{ $item['correct'] }}</td>
                        <td class="centered">{{ $item['mps'] ?? $item['percentage'] }}%</td>
                        <td class="centered">{{ $item['mastery_level'] }}</td>
                        <td class="centered">{{ $item['cause'] }}</td>
                    </tr>
                @endforeach

                <!-- Excel-style TOTAL ROW -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total</strong></td> <!-- Changed from 3 empty cells -->
                    <td><strong>{{ $totalCorrect }}</strong></td>
                    <td colspan="3"></td> <!-- Adjust colspan from 5 to 3 -->
                </tr>

                <!-- Number of Examinees -->
                <tr class="summary-row">
                    <td colspan="3" style="text-align: right;"><strong>N</strong></td>
                    <td><strong>{{ $itemAnalysis[0]['number_of_examinees'] ?? 0 }}</strong></td>
                    <td colspan="3"></td>
                </tr>

                <!-- Mean -->
                <tr class="summary-row">
                    <td colspan="3" style="text-align: right;"><strong>MEAN</strong></td>
                    <td>
                        <strong>
                            {{ $mean }}
                        </strong>
                    </td>
                    <td colspan="3"></td>
                </tr>

                <!-- MPS -->
                <tr class="summary-row">
                    <td colspan="3" style="text-align: right;"><strong>MPS</strong></td>
                    <td>
                        <strong>
                            {{ number_format($mps, 2) }}
                        </strong>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
        <!--<div class="overall-feedback-box">
            <h3>Overall Assessment Feedback</h3>
            <p>{{ $overallFeedback }}</p>
        </div>-->
        <p class="p-by"><strong>Prepared by:</strong> {{ $itemAnalysis[0]['teachername'] ?? '' }} - {{ $itemAnalysis[0]['teacherposition'] ?? '' }}</p>
    </div>
</div>
@endsection