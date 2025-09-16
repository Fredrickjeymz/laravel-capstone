@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Dashboard</h2>
        <p>Welcome back to Sepnas Assessment Generator, Admin!</p>
    </div>
    <div class="dashboard-containers">
        <div class="stat-card">
            <div class="con-ti">
                <h4>Generated Assessments</h4>
                <h4><i class="fas fa-file-alt"></i></h4>
            </div>
            <h1>{{ $assessmentCount }}</h1>
            <p>Created this month, Across all educators</p>
        </div>
        <div class="stat-card">
            <div class="con-ti">
                <h4>Questions Created </h4>
                <h4><i class="fas fa-question-circle"></i></h4>
             </div>
            <h1>{{ $questionCount }}</h1>
            <p>Across all assessment</p>
        </div>
        <div class="stat-card">
            <div class="con-ti">
                <h4>Ecuducators</h4>
                <h4><i class="fas fa-chalkboard-teacher"></i></h4>
            </div>
            <h1>{{ $teacherCount }}</h1>
            <p>Total Educators Account Registered</p>
        </div>
        <div class="stat-card">
            <div class="con-ti">
                <h4>Question Types </h4>
                <h4><i class="fas fa-list-ul"></i></h4>
            </div>
            <h1>{{ $questionTypeCount }}</h1>
            <p>Across all Assessment Type</p>
        </div>
    </div>

    <div class="lower-cons">
        <div class="bar-chart-con">
            <h2>Monthly Assessments</h2>
            <p class="sub-text">Monthly Assessment creation across all educators.</p>
            <div id="barChart" class="bar-chart"></div>
        </div>
        <script>
            function renderChartIfReady() {
                const chartContainer = document.querySelector("#barChart");
                if (!chartContainer) {
                    setTimeout(renderChartIfReady, 100); // Retry every 100ms
                    return;
                }

                const monthLabels = @json($monthLabels);
                const assessmentCounts = @json($assessmentCounts);

                const options = {
                    chart: {
                        type: 'bar',
                        height: 230
                    },
                    series: [{
                        name: 'Assessments',
                        data: assessmentCounts
                    }],
                    xaxis: {
                        categories: monthLabels
                    },
                    title: {
                        align: 'center'
                    },
                    colors: ['#9cfda4']
                };

                const chart = new ApexCharts(chartContainer, options);
                chart.render();
            }

            renderChartIfReady();
        </script>
        <div class="recent-activity">
             <h2>Recent Assessment Creation</h2>
                <p class="subtext">Recent assessment creation history</p>

                @foreach ($generatedAssessments->take(5) as $assessment)
                    <div class="activity-item">
                        <div class="icon-wrapper">
                            <svg class="doc-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8">
                                <path d="M9 12h6M9 16h6M8 4h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                            </svg>
                        </div>
                        <div class="details">
                            <div class="title">{{ $assessment->title ?? 'Untitled Assessment' }}</div>
                            <div class="type">{{ ucfirst(str_replace('_', ' ', $assessment->teacher->name)) }}</div>
                        </div>
                        <div class="timestamp">{{ $assessment->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
    
            </div>
        </div>
    </div>
</div>
@endsection
