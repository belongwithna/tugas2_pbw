<?php
function hitungNilaiAkhir($tugas, $uts, $uas)
{
    return ($tugas * 0.30) + ($uts * 0.30) + ($uas * 0.40);
}

function tentukanGrade($nilaiAkhir)
{
    if ($nilaiAkhir >= 85) {
        return "A";
    } elseif ($nilaiAkhir >= 75) {
        return "B";
    } elseif ($nilaiAkhir >= 65) {
        return "C";
    } elseif ($nilaiAkhir >= 50) {
        return "D";
    } else {
        return "E";
    }
}

function tentukanStatus($nilaiAkhir)
{
    if ($nilaiAkhir >= 65) {
        return "Lulus";
    }

    return "Tidak Lulus";
}

function tentukanPredikat($nilaiAkhir)
{
    if ($nilaiAkhir >= 85) {
        return "Sangat Baik";
    } elseif ($nilaiAkhir >= 75) {
        return "Baik";
    } elseif ($nilaiAkhir >= 65) {
        return "Cukup";
    }

    return "Perlu Ditingkatkan";
}

function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

$hasilMahasiswa = [];
$pesanError = [];

$namaList = (isset($_POST['nama']) && is_array($_POST['nama'])) ? $_POST['nama'] : [''];
$nimList = (isset($_POST['nim']) && is_array($_POST['nim'])) ? $_POST['nim'] : [''];
$tugasList = (isset($_POST['tugas']) && is_array($_POST['tugas'])) ? $_POST['tugas'] : [''];
$utsList = (isset($_POST['uts']) && is_array($_POST['uts'])) ? $_POST['uts'] : [''];
$uasList = (isset($_POST['uas']) && is_array($_POST['uas'])) ? $_POST['uas'] : [''];

$jumlahBaris = max(1, count($namaList), count($nimList), count($tugasList), count($utsList), count($uasList));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adaData = false;

    for ($i = 0; $i < $jumlahBaris; $i++) {
        $nama = trim(is_scalar($namaList[$i] ?? '') ? (string) ($namaList[$i] ?? '') : '');
        $nim = trim(is_scalar($nimList[$i] ?? '') ? (string) ($nimList[$i] ?? '') : '');
        $tugasInput = trim(is_scalar($tugasList[$i] ?? '') ? (string) ($tugasList[$i] ?? '') : '');
        $utsInput = trim(is_scalar($utsList[$i] ?? '') ? (string) ($utsList[$i] ?? '') : '');
        $uasInput = trim(is_scalar($uasList[$i] ?? '') ? (string) ($uasList[$i] ?? '') : '');

        if ($nama === '' && $nim === '' && $tugasInput === '' && $utsInput === '' && $uasInput === '') {
            continue;
        }

        $adaData = true;
        $masalah = [];

        if ($nama === '') {
            $masalah[] = 'nama belum diisi';
        }

        if ($nim === '') {
            $masalah[] = 'NIM belum diisi';
        }

        $daftarNilai = [
            'Tugas' => $tugasInput,
            'UTS' => $utsInput,
            'UAS' => $uasInput
        ];

        foreach ($daftarNilai as $jenisNilai => $nilaiInput) {
            if ($nilaiInput === '') {
                $masalah[] = 'nilai ' . $jenisNilai . ' belum diisi';
            } elseif (!is_numeric($nilaiInput)) {
                $masalah[] = 'nilai ' . $jenisNilai . ' harus berupa angka';
            } elseif ((float) $nilaiInput < 0 || (float) $nilaiInput > 100) {
                $masalah[] = 'nilai ' . $jenisNilai . ' harus berada pada rentang 0 sampai 100';
            }
        }

        if (!empty($masalah)) {
            $pesanError[] = 'Data mahasiswa baris ' . ($i + 1) . ': ' . implode(', ', $masalah) . '.';
            continue;
        }

        $tugas = (float) $tugasInput;
        $uts = (float) $utsInput;
        $uas = (float) $uasInput;

        $nilaiAkhir = hitungNilaiAkhir($tugas, $uts, $uas);

        $hasilMahasiswa[] = [
            'nama' => $nama,
            'nim' => $nim,
            'tugas' => $tugas,
            'uts' => $uts,
            'uas' => $uas,
            'nilaiAkhir' => $nilaiAkhir,
            'grade' => tentukanGrade($nilaiAkhir),
            'status' => tentukanStatus($nilaiAkhir),
            'predikat' => tentukanPredikat($nilaiAkhir)
        ];
    }

    if (!$adaData) {
        $pesanError[] = 'Silakan isi minimal satu data mahasiswa terlebih dahulu.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penghitung Nilai Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <header class="header">
            <h1>Penghitung Nilai Mahasiswa</h1>
            <p>Masukkan data mahasiswa untuk menghitung nilai akhir, grade, predikat, dan status kelulusan.</p>
        </header>

        <section class="panel">
            <h2>Input Data Mahasiswa</h2>
            <p class="petunjuk">Nilai tugas, UTS, dan UAS harus berada pada rentang 0 sampai 100. Gunakan tombol “Tambah Mahasiswa” untuk menambah baris.</p>

            <?php if (!empty($pesanError)): ?>
                <div class="pesan-error">
                    <strong>Periksa kembali data berikut:</strong>
                    <?php foreach ($pesanError as $pesan): ?>
                        <p>• <?= e($pesan) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div id="daftar-input">
                    <?php for ($i = 0; $i < $jumlahBaris; $i++): ?>
                        <div class="input-row">
                            <div class="field">
                                <label>Nama Mahasiswa</label>
                                <input type="text" name="nama[]" placeholder="Contoh: Andi" value="<?= e($namaList[$i] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>NIM</label>
                                <input type="text" name="nim[]" placeholder="Nomor induk" value="<?= e($nimList[$i] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>Nilai Tugas</label>
                                <input type="number" name="tugas[]" min="0" max="100" step="0.01" placeholder="0-100" value="<?= e($tugasList[$i] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>Nilai UTS</label>
                                <input type="number" name="uts[]" min="0" max="100" step="0.01" placeholder="0-100" value="<?= e($utsList[$i] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>Nilai UAS</label>
                                <input type="number" name="uas[]" min="0" max="100" step="0.01" placeholder="0-100" value="<?= e($uasList[$i] ?? '') ?>">
                            </div>
                            <button type="button" class="btn-hapus hapus-baris">Hapus</button>
                        </div>
                    <?php endfor; ?>
                </div>

                <button type="button" class="btn-tambah" id="tambah-baris">+ Tambah Mahasiswa</button>
                <br>
                <button type="submit" class="btn-hitung">Hitung Nilai</button>
            </form>
        </section>

        <?php if (!empty($hasilMahasiswa)): ?>
            <section class="panel">
                <h2>Hasil Perhitungan</h2>
                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Tugas</th>
                                <th>UTS</th>
                                <th>UAS</th>
                                <th>Nilai Akhir</th>
                                <th>Grade</th>
                                <th>Predikat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hasilMahasiswa as $nomor => $mhs): ?>
                                <tr>
                                    <td><?= $nomor + 1 ?></td>
                                    <td><?= e($mhs['nama']) ?></td>
                                    <td><?= e($mhs['nim']) ?></td>
                                    <td><?= number_format($mhs['tugas'], 2, ',', '.') ?></td>
                                    <td><?= number_format($mhs['uts'], 2, ',', '.') ?></td>
                                    <td><?= number_format($mhs['uas'], 2, ',', '.') ?></td>
                                    <td><strong><?= number_format($mhs['nilaiAkhir'], 2, ',', '.') ?></strong></td>
                                    <td><strong><?= e($mhs['grade']) ?></strong></td>
                                    <td><?= e($mhs['predikat']) ?></td>
                                    <td class="<?= $mhs['status'] === 'Lulus' ? 'status-lulus' : 'status-tidak' ?>"><?= e($mhs['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="catatan">Rumus: (Tugas × 30%) + (UTS × 30%) + (UAS × 40%). Mahasiswa dinyatakan lulus jika nilai akhir minimal 65.</p>
            </section>
        <?php endif; ?>
    </main>

    <script>
        const daftarInput = document.getElementById('daftar-input');
        const tombolTambah = document.getElementById('tambah-baris');

        tombolTambah.addEventListener('click', function () {
            const barisBaru = document.createElement('div');
            barisBaru.className = 'input-row';
            barisBaru.innerHTML = `
                <div class="field">
                    <label>Nama Mahasiswa</label>
                    <input type="text" name="nama[]" placeholder="Contoh: Andi">
                </div>
                <div class="field">
                    <label>NIM</label>
                    <input type="text" name="nim[]" placeholder="Nomor induk">
                </div>
                <div class="field">
                    <label>Nilai Tugas</label>
                    <input type="number" name="tugas[]" min="0" max="100" step="0.01" placeholder="0-100">
                </div>
                <div class="field">
                    <label>Nilai UTS</label>
                    <input type="number" name="uts[]" min="0" max="100" step="0.01" placeholder="0-100">
                </div>
                <div class="field">
                    <label>Nilai UAS</label>
                    <input type="number" name="uas[]" min="0" max="100" step="0.01" placeholder="0-100">
                </div>
                <button type="button" class="btn-hapus hapus-baris">Hapus</button>
            `;
            daftarInput.appendChild(barisBaru);
        });

        daftarInput.addEventListener('click', function (event) {
            if (event.target.classList.contains('hapus-baris')) {
                const semuaBaris = daftarInput.querySelectorAll('.input-row');

                if (semuaBaris.length > 1) {
                    event.target.closest('.input-row').remove();
                } else {
                    semuaBaris[0].querySelectorAll('input').forEach(function (input) {
                        input.value = '';
                    });
                }
            }
        });
    </script>
</body>
</html>
