<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEPNAS FAG</title>
    <link rel="stylesheet" href="{{ asset('css/OutsideMainLayout.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .main-container {
            background: 
                linear-gradient(rgba(200, 200, 200, 0.9), rgba(200, 200, 200, 0.9)), 
                url("{{ asset('image/image2.jpg') }}") no-repeat center center;
            background-size: cover;
            width: 100vw;
            height: 87vh;
        }
        input::placeholder {
            color: #808080;
        }
        
        select::placeholder {
            color: #808080; 
        }
    </style>
</head>
<body>
    
    <div class="w-container">
        <div class="header">
            <img src="{{ asset('image/sepnas_logo.png') }}" class="school-logo" alt="school logo">
            <h1 id="btn-return-home" data-url="{{ route('start') }}"> SEPNAS <span class="small-text">Formative Assessment Generator</span></h1>
        </div>
        <div class="main-container" id="main-area">
        @section('main-area')

        @show
        </div>
    </div>

   
</body>
<script src="{{ asset('js/outsidenav.js') }}"></script>
<script src="{{ asset('js/change-pass-teacher.js') }}"></script>
<script src="{{ asset('js/change-pass-student.js') }}"></script>
</html>