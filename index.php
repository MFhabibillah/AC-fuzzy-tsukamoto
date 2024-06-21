<!DOCTYPE html>
<html>

<head>
    <title>Sistem Fuzzy Kipas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .result h2 {
            color: #4CAF50;
        }

        .result {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Sistem Fuzzy Kipas</h1>
        <form method="post">
            <label>Temperatur (°C):</label>
            <input type="number" name="temperatur" step="0.1" required><br>

            <label>Jumlah Orang:</label>
            <input type="number" name="orang" required><br>

            <label>Kelembaban (%):</label>
            <input type="number" name="kelembaban" step="0.1" required><br>

            <input type="submit" value="Hitung Kecepatan Kipas">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $temperatur = floatval($_POST['temperatur']);
            $orang = intval($_POST['orang']);
            $kelembaban = floatval($_POST['kelembaban']);

            $keanggotaanTemperatur = fuzzifikasiTemperatur($temperatur);
            $keanggotaanOrang = fuzzifikasiOrang($orang);
            $keanggotaanKelembaban = fuzzifikasiKelembaban($kelembaban);

            echo "<div class='result'>";
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

            $aturan = evaluasiAturan($keanggotaanTemperatur, $keanggotaanOrang, $keanggotaanKelembaban);
            $kecepatanKipas = defuzzifikasi($aturan);
            $kecepatanKipasTeks = kecepatanKipasTeks($kecepatanKipas);

            echo "<h2>Hasil</h2>";
            echo "Kecepatan Kipas: $kecepatanKipas ($kecepatanKipasTeks)<br>";
            echo "</div>";
        }

        function hitungDerajatKeanggotaan($nilai, $min, $max)
        {
            if ($nilai <= $min) return 0;
            if ($nilai >= $max) return 1;
            return ($nilai - $min) / ($max - $min);
        }

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

        function kecepatanKipasTeks($kecepatan)
        {
            if ($kecepatan <= 20) return 'Sangat Lambat';
            if ($kecepatan <= 40) return 'Lambat';
            if ($kecepatan <= 60) return 'Sedang';
            if ($kecepatan <= 80) return 'Cepat';
            return 'Sangat Cepat';
        }

        function evaluasiAturan($keanggotaanTemperatur, $keanggotaanOrang, $keanggotaanKelembaban)
        {
            $aturan = [];

            $aturan['sangat_lambat'] = min($keanggotaanTemperatur['dingin'], $keanggotaanOrang['sangat_sedikit'], $keanggotaanKelembaban['kering']);
            $aturan['lambat'] = min($keanggotaanTemperatur['sejuk'], $keanggotaanOrang['sedikit'], $keanggotaanKelembaban['normal']);
            $aturan['sedang'] = min($keanggotaanTemperatur['normal'], $keanggotaanOrang['sedang'], $keanggotaanKelembaban['normal']);
            $aturan['cepat'] = min($keanggotaanTemperatur['hangat'], $keanggotaanOrang['banyak'], $keanggotaanKelembaban['lembab']);
            $aturan['sangat_cepat'] = min($keanggotaanTemperatur['panas'], $keanggotaanOrang['sangat_banyak'], $keanggotaanKelembaban['lembab']);

            $aturan['lambat'] = max($aturan['lambat'], min($keanggotaanTemperatur['dingin'], $keanggotaanOrang['sedang'], $keanggotaanKelembaban['kering']));
            $aturan['sedang'] = max($aturan['sedang'], min($keanggotaanTemperatur['sejuk'], $keanggotaanOrang['banyak'], $keanggotaanKelembaban['normal']));
            $aturan['cepat'] = max($aturan['cepat'], min($keanggotaanTemperatur['normal'], $keanggotaanOrang['sangat_banyak'], $keanggotaanKelembaban['lembab']));
            $aturan['sangat_cepat'] = max($aturan['sangat_cepat'], min($keanggotaanTemperatur['hangat'], $keanggotaanOrang['sangat_sedikit'], $keanggotaanKelembaban['kering']));

            return $aturan;
        }
        ?>
    </div>
</body>

</html>