$(document).ready(function () {
    $(".assessment-buttons button").on("click", function () {
        $(".assessment-buttons button").removeClass("active");
        $(this).addClass("active");
    });
});

document.getElementById('search-btn').addEventListener('click', function () {
    const query = document.getElementById('search-input').value.trim();
    const url = new URL(window.location.href);
    url.searchParams.set('search', query);
    url.searchParams.delete('page'); // optional: reset pagination on search
    window.location.href = url.toString();
});

document.getElementById('search-input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('search-btn').click();
    }
});