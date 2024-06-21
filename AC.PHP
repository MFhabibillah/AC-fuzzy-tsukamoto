<!DOCTYPE html>
<html>
<head>
    <title>Sistem Fuzzy Kipas</title>
</head>
<body>
    <h1>Sistem Fuzzy Kipas</h1>
    <form method="post">
        <label>Temperatur (°C):</label><br>
        <input type="number" name="temperatur" step="0.1" required><br><br>
        <label>Jumlah Orang:</label><br>
        <input type="number" name="orang" required><br><br>
        <label>Kelembaban (%):</label><br>
        <input type="number" name="kelembaban" step="0.1" required><br><br>
        <input type="submit" value="Hitung Kecepatan Kipas">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Input
        $temperatur = floatval($_POST['temperatur']);
        $orang = intval($_POST['orang']);
        $kelembaban = floatval($_POST['kelembaban']);

        // Fuzzifikasi
        $keanggotaanTemperatur = fuzzifikasiTemperatur($temperatur);
        $keanggotaanOrang = fuzzifikasiOrang($orang);
        $keanggotaanKelembaban = fuzzifikasiKelembaban($kelembaban);

        // Tampilkan derajat keanggotaan dari setiap inputan
        echo "<h2>Derajat Keanggotaan</h2>";
        echo "<h3>Temperatur</h3>";
        foreach ($keanggotaanTemperatur as $kategori => $derajat) {
            echo "$kategori: $derajat<br>";
        }
        echo "<h3>Jumlah Orang</h3>";
        foreach ($keanggotaanOrang as $kategori => $derajat) {
            echo "$kategori: $derajat<br>";
        }
        echo "<h3>Kelembaban</h3>";
        foreach ($keanggotaanKelembaban as $kategori => $derajat) {
            echo "$kategori: $derajat<br>";
        }

        // Evaluasi aturan
        $aturan = evaluasiAturan($keanggotaanTemperatur, $keanggotaanOrang, $keanggotaanKelembaban);

        // Defuzzifikasi
        $kecepatanKipas = defuzzifikasi($aturan);

        // Konversi kecepatan kipas menjadi teks
        $kecepatanKipasTeks = kecepatanKipasTeks($kecepatanKipas);

        // Tampilkan hasil
        echo "<h2>Hasil</h2>";
        echo "Kecepatan Kipas: $kecepatanKipas ($kecepatanKipasTeks)<br>";
    }
    ?>

    <?php
    // Fungsi untuk menghitung derajat keanggotaan
    function hitungDerajatKeanggotaan($nilai, $min, $max)
    {
        if ($nilai <= $min) return 0;
        if ($nilai >= $max) return 1;
        return ($nilai - $min) / ($max - $min);
    }

    // Fuzzifikasi
    function fuzzifikasiTemperatur($temperatur)
    {
        return [
            'dingin' => 1 - hitungDerajatKeanggotaan($temperatur, 17, 22),
            'sejuk' => hitungDerajatKeanggotaan($temperatur, 22, 27),
            'normal' => hitungDerajatKeanggotaan($temperatur, 27, 32),
            'hangat' => hitungDerajatKeanggotaan($temperatur, 32, 37),
            'panas' => hitungDerajatKeanggotaan($temperatur, 37, 42)
        ];
    }

    function fuzzifikasiOrang($orang)
    {
        return [
            'sangat_sedikit' => 1 - hitungDerajatKeanggotaan($orang, 0, 5),
            'sedikit' => hitungDerajatKeanggotaan($orang, 5, 10),
            'sedang' => hitungDerajatKeanggotaan($orang, 10, 20),
            'banyak' => hitungDerajatKeanggotaan($orang, 20, 30),
            'sangat_banyak' => hitungDerajatKeanggotaan($orang, 30, 40)
        ];
    }

    function fuzzifikasiKelembaban($kelembaban)
    {
        return [
            'kering' => 1 - hitungDerajatKeanggotaan($kelembaban, 0, 30),
            'normal' => hitungDerajatKeanggotaan($kelembaban, 30, 60),
            'lembab' => hitungDerajatKeanggotaan($kelembaban, 60, 100)
        ];
    }

    // Defuzzifikasi menggunakan metode Centroid
    function defuzzifikasi($aturan)
    {
        $pembilang = 0;
        $penyebut = 0;

        foreach ($aturan as $output => $derajat) {
            $nilai = 0;
            switch ($output) {
                case 'sangat_lambat':
                    $nilai = 20;
                    break;
                case 'lambat':
                    $nilai = 40;
                    break;
                case 'sedang':
                    $nilai = 60;
                    break;
                case 'cepat':
                    $nilai =
                    $nilai = 80;
                    break;
                case 'sangat_cepat':
                    $nilai = 100;
                    break;
            }
            $pembilang += $derajat * $nilai;
            $penyebut += $derajat;
        }

        return ($penyebut == 0) ? 0 : ($pembilang / $penyebut);
    }

    // Mengubah kecepatan kipas menjadi deskripsi teks
    function kecepatanKipasTeks($kecepatan)
    {
        if ($kecepatan <= 20) return 'Sangat Lambat';
        if ($kecepatan <= 40) return 'Lambat';
        if ($kecepatan <= 60) return 'Sedang';
        if ($kecepatan <= 80) return 'Cepat';
        return 'Sangat Cepat';
    }

    // Menentukan aturan
    function evaluasiAturan($keanggotaanTemperatur, $keanggotaanOrang, $keanggotaanKelembaban)
    {
        $aturan = [];

        // Contoh aturan: Jika temperatur 'sejuk', orang 'sedikit', dan kelembaban 'normal', maka kipas 'lambat'
        $aturan['sangat_lambat'] = min($keanggotaanTemperatur['dingin'], $keanggotaanOrang['sangat_sedikit'], $keanggotaanKelembaban['kering']);
        $aturan['lambat'] = min($keanggotaanTemperatur['sejuk'], $keanggotaanOrang['sedikit'], $keanggotaanKelembaban['normal']);
        $aturan['sedang'] = min($keanggotaanTemperatur['normal'], $keanggotaanOrang['sedang'], $keanggotaanKelembaban['normal']);
        $aturan['cepat'] = min($keanggotaanTemperatur['hangat'], $keanggotaanOrang['banyak'], $keanggotaanKelembaban['lembab']);
        $aturan['sangat_cepat'] = min($keanggotaanTemperatur['panas'], $keanggotaanOrang['sangat_banyak'], $keanggotaanKelembaban['lembab']);

        // Menambahkan aturan tambahan untuk kombinasi input lainnya
        $aturan['lambat'] = max($aturan['lambat'], min($keanggotaanTemperatur['dingin'], $keanggotaanOrang['sedang'], $keanggotaanKelembaban['kering']));
        $aturan['sedang'] = max($aturan['sedang'], min($keanggotaanTemperatur['sejuk'], $keanggotaanOrang['banyak'], $keanggotaanKelembaban['normal']));
        $aturan['cepat'] = max($aturan['cepat'], min($keanggotaanTemperatur['normal'], $keanggotaanOrang['sangat_banyak'], $keanggotaanKelembaban['lembab']));
        $aturan['sangat_cepat'] = max($aturan['sangat_cepat'], min($keanggotaanTemperatur['hangat'], $keanggotaanOrang['sangat_sedikit'], $keanggotaanKelembaban['kering']));

        return $aturan;
    }
    ?>
</body>
</html>
