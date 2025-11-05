<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Assessment Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <ul>
        <li><a href="#">Konseling Online</a></li>
        <li><a href="#">Daily Reflection</a></li>
        <li class="active"><a href="#">Assessment Test</a></li>
        <li><a href="#">Konten Edukasi</a></li>
        <li><a href="#">Laporkan Kasus</a></li>
        <li><a href="#">Mood Tracker</a></li>
        <li><a href="#">Wellness Mission</a></li>
        <li class="search">🔍</li>
    </ul>
</nav>

<div class="container">
    <h1>Apa yang sedang kamu rasakan?</h1>
    <p>Yuk, pilih perasaan yang sedang kamu hadapi dan temukan bantuan yang kamu butuhkan sekarang!</p>

    <form method="POST" action="form.php">
        <div class="feeling-options">
<label class="feeling-box">
    <input type="radio" name="feeling" value="senang" required>
    <span>😊</span>
</label>
<label class="feeling-box">
    <input type="radio" name="feeling" value="sedih">
    <span>😢</span>
</label>
<label class="feeling-box">
    <input type="radio" name="feeling" value="cemas">
    <span>😟</span>
</label>
<label class="feeling-box">
    <input type="radio" name="feeling" value="marah">
    <span>😡</span>
</label>
</div>

        <div class="big-box">
            <button class="button">Test</button>
        </div>
    </form>
</div>

</body>
</html>
