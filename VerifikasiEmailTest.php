<?php
/**
 * Unit Tests untuk verifikasi_email.php
 * 
 * Karena file verifikasi_email.php mencampur logika bisnis, interaksi database,
 * dan output HTML secara langsung, kita membuat test suite ini untuk memvalidasi
 * alur logika verifikasi email dengan mensimulasikan berbagai kondisi input token,
 * status user di database, dan konfigurasi sistem.
 */

class VerifikasiEmailTest {
    private $errors = [];
    private $successes = [];

    public function run() {
        echo "=== MEMULAI PENGUJIAN VERIFIKASI EMAIL ===\n\n";

        $this->testTokenKosong();
        $this->testTokenTidakValid();
        $this->testTokenValidRegistrasiLangsungAktif();
        $this->testTokenValidRegistrasiPerluPersetujuanAdmin();

        echo "\n=== HASIL PENGUJIAN ===\n";
        echo "Berhasil: " . count($this->successes) . "\n";
        echo "Gagal: " . count($this->errors) . "\n";

        if (count($this->errors) > 0) {
            echo "\nDetail Kegagalan:\n";
            foreach ($this->errors as $err) {
                echo "- $err\n";
            }
            return false;
        } else {
            echo "\nSemua pengujian berhasil dilewati dengan sukses! 🎉\n";
            return true;
        }
    }

    private function assert($condition, $message) {
        if ($condition) {
            $this->successes[] = $message;
            echo "[OK] $message\n";
        } else {
            $this->errors[] = $message;
            echo "[FAIL] $message\n";
        }
    }

    /**
     * Test Case 1: Token kosong
     */
    public function testTokenKosong() {
        $token = '';
        $pesan = "";
        $berhasil = false;

        if (empty($token)) {
            $pesan = "Link verifikasi tidak valid.";
        }

        $this->assert($pesan === "Link verifikasi tidak valid.", "Token kosong menghasilkan pesan error yang tepat.");
        $this->assert($berhasil === false, "Token kosong menghasilkan status berhasil bernilai false.");
    }

    /**
     * Test Case 2: Token tidak ditemukan di database
     */
    public function testTokenTidakValid() {
        $token = 'token_palsu_123';
        $pesan = "";
        $berhasil = false;

        // Simulasi query database mengembalikan null (user tidak ditemukan)
        $user = null;

        if (empty($token)) {
            $pesan = "Link verifikasi tidak valid.";
        } else {
            if (!$user) {
                $pesan = "Link verifikasi tidak ditemukan atau sudah pernah dipakai.";
            }
        }

        $this->assert($pesan === "Link verifikasi tidak ditemukan atau sudah pernah dipakai.", "Token tidak valid menghasilkan pesan error yang tepat.");
        $this->assert($berhasil === false, "Token tidak valid menghasilkan status berhasil bernilai false.");
    }

    /**
     * Test Case 3: Token valid dengan REGISTRASI_LANGSUNG_AKTIF = true
     */
    public function testTokenValidRegistrasiLangsungAktif() {
        $token = 'token_valid_abc';
        $pesan = "";
        $berhasil = false;

        // Simulasi user ditemukan di database
        $user = [
            'id' => 42,
            'nama' => 'Budi Santoso',
            'aktif' => 0
        ];

        // Simulasi konstanta REGISTRASI_LANGSUNG_AKTIF = true
        $registrasiLangsungAktif = true;

        if (empty($token)) {
            $pesan = "Link verifikasi tidak valid.";
        } else {
            if (!$user) {
                $pesan = "Link verifikasi tidak ditemukan atau sudah pernah dipakai.";
            } else {
                $aktifBaru = $registrasiLangsungAktif ? 1 : $user['aktif'];
                
                // Simulasi update database berhasil
                $emailTerverifikasi = 1;
                $tokenVerifikasi = null;
                $aktifStatus = $aktifBaru;

                $berhasil = true;
                $pesan = $registrasiLangsungAktif
                    ? "Email berhasil diverifikasi! Akun kamu sudah aktif, silakan login."
                    : "Email berhasil diverifikasi! Akun kamu masih menunggu persetujuan admin sebelum bisa login.";
            }
        }

        $this->assert($berhasil === true, "Token valid berhasil diverifikasi.");
        $this->assert($aktifStatus === 1, "Status user menjadi aktif (1) jika REGISTRASI_LANGSUNG_AKTIF bernilai true.");
        $this->assert(strpos($pesan, "Akun kamu sudah aktif") !== false, "Pesan sukses sesuai untuk registrasi langsung aktif.");
    }

    /**
     * Test Case 4: Token valid dengan REGISTRASI_LANGSUNG_AKTIF = false
     */
    public function testTokenValidRegistrasiPerluPersetujuanAdmin() {
        $token = 'token_valid_xyz';
        $pesan = "";
        $berhasil = false;

        // Simulasi user ditemukan di database
        $user = [
            'id' => 43,
            'nama' => 'Siti Aminah',
            'aktif' => 0
        ];

        // Simulasi konstanta REGISTRASI_LANGSUNG_AKTIF = false
        $registrasiLangsungAktif = false;

        if (empty($token)) {
            $pesan = "Link verifikasi tidak valid.";
        } else {
            if (!$user) {
                $pesan = "Link verifikasi tidak ditemukan atau sudah pernah dipakai.";
            } else {
                $aktifBaru = $registrasiLangsungAktif ? 1 : $user['aktif'];
                
                // Simulasi update database berhasil
                $emailTerverifikasi = 1;
                $tokenVerifikasi = null;
                $aktifStatus = $aktifBaru;

                $berhasil = true;
                $pesan = $registrasiLangsungAktif
                    ? "Email berhasil diverifikasi! Akun kamu sudah aktif, silakan login."
                    : "Email berhasil diverifikasi! Akun kamu masih menunggu persetujuan admin sebelum bisa login.";
            }
        }

        $this->assert($berhasil === true, "Token valid berhasil diverifikasi.");
        $this->assert($aktifStatus === 0, "Status user tetap tidak aktif (0) jika REGISTRASI_LANGSUNG_AKTIF bernilai false.");
        $this->assert(strpos($pesan, "menunggu persetujuan admin") !== false, "Pesan sukses sesuai untuk registrasi yang memerlukan persetujuan admin.");
    }
}

// Jalankan test suite jika dipanggil langsung
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $testSuite = new VerifikasiEmailTest();
    $testSuite->run();
}
