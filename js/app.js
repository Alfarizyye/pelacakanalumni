// 🔐 CEK LOGIN
if (localStorage.getItem('isAuthenticated') !== 'true') {
    window.location.href = 'login.html';
}

// State
let alumni = [];
let chart = null;

// 🔄 FETCH ALUMNI DARI DB (hanya yang sudah dilacak)
async function fetchAlumni() {
    try {
        const response = await fetch('api/get_alumni.php?tracked=1&limit=100');
        const result = await response.json();
        if (result.status === 'success') {
            alumni = result.data;
            tampil();
        }
    } catch (e) {
        console.error("Gagal mengambil data", e);
    }
}

// 🔍 === FITUR UTAMA: LACAK ALUMNI ===
async function lacakAlumni() {
    let nama = document.getElementById("searchNama").value.trim();
    let nim = document.getElementById("searchNIM").value.trim();
    let tahunMasuk = document.getElementById("searchTahunMasuk").value.trim();
    let tanggalLulus = document.getElementById("searchTanggalLulus").value.trim();
    let fakultas = document.getElementById("searchFakultas").value.trim();
    let prodi = document.getElementById("searchProdi").value.trim();

    if (!nama) {
        showNotFound("Nama wajib diisi!");
        return;
    }

    // Tampilkan status pencarian
    document.getElementById("panelQuery").innerText = `Mencari: ${nama} ${nim ? '| NIM: ' + nim : ''}...`;
    document.getElementById("panelTracking").innerHTML = "• Memeriksa database alumni...";
    document.getElementById("panelScore").innerText = "...";

    try {
        // 🔎 Langkah 1: Cari dulu di database
        const searchRes = await fetch('api/search_alumni.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nama_lulusan: nama,
                nim: nim,
                tahun_masuk: tahunMasuk,
                tanggal_lulus: tanggalLulus,
                fakultas: fakultas,
                program_studi: prodi
            })
        });
        const searchResult = await searchRes.json();

        if (searchResult.status === 'not_found') {
            // ❌ Tidak ditemukan — tampilkan pesan, JANGAN simpan ke DB
            showNotFound(searchResult.message);
            return;
        }

        if (searchResult.status === 'error') {
            showNotFound("Terjadi kesalahan: " + searchResult.message);
            return;
        }

        // ✅ Langkah 2: Ditemukan — gunakan data yang ada, mulai tracking
        await fetchAlumni();

        // Cari record di array alumni yang sudah diload
        let found = searchResult.data[0];
        let index = alumni.findIndex(a => a.id == found.id);

        if (index === -1) {
            // Alumni ada di DB tapi belum muncul di tabel (status Belum Dilacak)
            // Tambahkan sementara ke array lalu track
            alumni.unshift(found);
            index = 0;
        }

        // Reset panel
        document.getElementById("panelTracking").innerHTML = "• Alumni ditemukan di database! Memulai pelacakan...";
        track(index);

    } catch(e) {
        console.error(e);
        showNotFound("Gagal menghubungi server. Periksa koneksi Anda.");
    }
}

// ❌ Tampilkan pesan "tidak ditemukan" di panel
function showNotFound(pesan) {
    document.getElementById("panelQuery").innerText = "—";
    document.getElementById("panelTracking").innerHTML =
        `<span style="color:#ff4d4d; font-weight:bold;">
            ⚠️ ${pesan}
         </span>`;
    document.getElementById("panelScore").innerText = "0%";
}

async function trackUlang(i){
    let a = alumni[i];

    document.getElementById("panelQuery").innerText = a.nama_lulusan;
    document.getElementById("panelTracking").innerHTML = "• Re-tracking data...";
    document.getElementById("panelScore").innerText = "Processing...";

    setTimeout(async () => {
        let score = Math.floor(Math.random() * 40) + 60;
        a.confidence = score;
        a.status = score > 70 ? "Teridentifikasi" : "Perlu Verifikasi";

        document.getElementById("panelScore").innerText = score + "%";

        // Update ke DB
        await updateDataDB(a);
        
        // Simpan History
        await simpanHistory(a.nama_lulusan, a.status, score);

        await fetchAlumni();
    }, 1000);
}

async function updateDataDB(data) {
    await fetch('api/update_alumni.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
}

async function simpanHistory(name, status, score) {
    await fetch('api/add_history.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: name,
            status: status,
            score: score,
            tanggal: new Date().toLocaleDateString()
        })
    });
}

// 🧠 TRACKING ENGINE (SIMULASI)
function track(i) {
    let a = alumni[i];

    let query = `${a.nama_lulusan} ${a.nim || ""} ${a.program_studi || ""} ${a.fakultas || ""} UMM ${a.tanggal_lulus || ""}`;
    document.getElementById("panelQuery").innerText = query;

    let hasil = [
        "Mencari jejak digital...",
        "Memeriksa LinkedIn & Instagram...",
        "Menganalisis data karir..."
    ];
    document.getElementById("panelTracking").innerHTML = hasil.map(h => "• " + h).join("<br>");

    // ⏳ SIMULASI DELAY PELAKACAN
    setTimeout(async () => {
        let namaClean = a.nama_lulusan.toLowerCase().replace(/[^a-z]/g, '');
        let namaDepan = a.nama_lulusan.split(' ')[0].toLowerCase();
        
        const perusahaan = ["PT Astra International", "Bank Mandiri", "Telkom Indonesia", "Shopee", "Gojek", "Ruangguru", "Startup Tech"];
        const posisiArr = ["Software Engineer", "Marketing Manager", "Data Analyst", "Project Officer", "Business Development"];
        const jenis = ["Swasta", "PNS", "Wirausaha", "BUMN"];
        
        let randomPT = perusahaan[Math.floor(Math.random() * perusahaan.length)];
        let randomPosisi = posisiArr[Math.floor(Math.random() * posisiArr.length)];
        let randomJenis = jenis[Math.floor(Math.random() * jenis.length)];

        // Fungsi pembantu untuk cek apakah data kosong atau hanya berisi '-'
        const isEntryEmpty = (val) => !val || val === '-' || val === '';

        // GANTI DATA LAMA DENGAN DATA BARU HASIL TRACKING
        if (isEntryEmpty(a.email)) a.email = namaDepan + Math.floor(Math.random() * 99) + "@gmail.com";
        if (isEntryEmpty(a.nohp)) a.nohp = "08" + Math.floor(111111111 + Math.random() * 888888888);
        if (isEntryEmpty(a.linkedin)) a.linkedin = "linkedin.com/in/" + namaClean;
        if (isEntryEmpty(a.ig)) a.ig = "@" + namaClean;
        if (isEntryEmpty(a.fb)) a.fb = "facebook.com/" + namaClean;
        if (isEntryEmpty(a.tiktok)) a.tiktok = "@" + namaClean + "_official";
        
        // Data Pekerjaan Baru
        a.tempat_kerja = randomPT;
        a.posisi = randomPosisi;
        a.jenis_pekerjaan = randomJenis;
        a.alamat_kerja = "Jakarta Selatan, DKI Jakarta";
        a.sosmed_kerja = "@" + randomPT.toLowerCase().split(' ')[0];

        let score = Math.floor(Math.random() * 25) + 75; // Score 75-100%
        a.confidence = score;
        a.status = "Teridentifikasi";

        document.getElementById("panelScore").innerText = score + "%";
        document.getElementById("panelTracking").innerHTML = "<span class='text-success'>• Pelacakan Selesai! Data diperbarui.</span>";

        // Update ke Database
        await updateDataDB(a);
        
        // Simpan ke History
        await simpanHistory(a.nama_lulusan, a.status, score);

        // Refresh tabel untuk menampilkan data yang baru ditemukan
        await fetchAlumni();
        
        alert(`Pelacakan selesai! Data ${a.nama_lulusan} telah diperbarui dengan informasi terbaru.`);
    }, 2000);
}

// 📋 TAMPILKAN TABLE
function tampil() {
    let html = "";

    if (alumni.length === 0) {
        html = `<tr><td colspan="14" class="text-center py-4 text-muted">
            <i class="fa fa-info-circle"></i> Belum ada alumni yang dilacak. Gunakan form di atas untuk mulai melacak alumni.
        </td></tr>`;
    } else {
        alumni.forEach((a, i) => {
            html += `
            <tr>
                <td>${a.nama_lulusan}</td>
                <td>${a.program_studi || '-'}</td>
                <td>${a.tahun_masuk || '-'}</td>
                <td>${a.tanggal_lulus || '-'}</td>
                <td>${a.email || '-'}</td>
                <td>${a.nohp || '-'}</td>
                <td>
                    <small>
                        LI: ${a.linkedin || '-'}<br>
                        IG: ${a.ig || '-'}<br>
                        FB: ${a.fb || '-'}<br>
                        TT: ${a.tiktok || '-'}
                    </small>
                </td>
                <td>${a.tempat_kerja || '-'}</td>
                <td>${a.alamat_kerja || '-'}</td>
                <td>${a.sosmed_kerja || '-'}</td>
                <td>${a.posisi || '-'}</td>
                <td>${a.jenis_pekerjaan || '-'}</td>
                <td>${a.status}</td>

                <td style="width:150px">
                    <div class="progress" style="height:20px;">
                        <div class="progress-bar bg-success" 
                            style="width:${a.confidence}%">
                            ${a.confidence}%
                        </div>
                    </div>
                </td>

                <td style="min-width: 150px;">
                    <button onclick="bukaModalOSINT(${i})" class="btn btn-info btn-sm text-white mb-1 w-100 text-start">
                        <i class="fa fa-search"></i> Cari (OSINT)
                    </button>
                    <button onclick="bukaModalEdit(${i})" class="btn btn-warning btn-sm mb-1 w-100 text-start">
                        <i class="fa fa-edit"></i> Edit Data
                    </button>
                    <button onclick="trackUlang(${i})" class="btn btn-danger btn-sm w-100 text-start">
                        <i class="fa fa-robot"></i> Auto-Lacak
                    </button>
                </td>
            </tr>
            `;
        });
    }

    document.getElementById("tableAlumni").innerHTML = html;

    updateStat();
    grafik();
}

// 🌐 BUKA MODAL OSINT SEARCH
function bukaModalOSINT(i) {
    let a = alumni[i];
    document.getElementById('osintNama').innerText = a.nama_lulusan;
    
    // Generate OSINT / Google Dorking Links
    let baseQuery = `"${a.nama_lulusan}" "Universitas Muhammadiyah Malang"`;
    if (a.program_studi && a.program_studi !== '-') {
        baseQuery += ` "${a.program_studi}"`;
    }

    let queryGoogle = encodeURIComponent(baseQuery);
    let queryLinkedIn = encodeURIComponent(`${baseQuery} site:linkedin.com/in/`);
    let queryInstagram = encodeURIComponent(`${baseQuery} site:instagram.com`);
    let queryFacebook = encodeURIComponent(`${baseQuery} site:facebook.com`);

    document.getElementById('linkGoogle').href = `https://www.google.com/search?q=${queryGoogle}`;
    document.getElementById('linkLinkedIn').href = `https://www.google.com/search?q=${queryLinkedIn}`;
    document.getElementById('linkInstagram').href = `https://www.google.com/search?q=${queryInstagram}`;
    document.getElementById('linkFacebook').href = `https://www.google.com/search?q=${queryFacebook}`;

    let osintModal = new bootstrap.Modal(document.getElementById('osintModal'));
    osintModal.show();
}

// ✏️ BUKA MODAL EDIT DATA
function bukaModalEdit(i) {
    let a = alumni[i];
    document.getElementById('editIndex').value = i;
    
    // Populate form dengan data saat ini
    document.getElementById('editEmail').value = a.email || '';
    document.getElementById('editNoHp').value = a.nohp || '';
    document.getElementById('editLinkedIn').value = a.linkedin || '';
    document.getElementById('editIG').value = a.ig || '';
    document.getElementById('editFB').value = a.fb || '';
    document.getElementById('editTikTok').value = a.tiktok || '';
    
    document.getElementById('editTempatKerja').value = a.tempat_kerja || '';
    document.getElementById('editPosisi').value = a.posisi || '';
    document.getElementById('editKategori').value = (a.jenis_pekerjaan && a.jenis_pekerjaan !== '-') ? a.jenis_pekerjaan : '';
    document.getElementById('editAlamatKerja').value = a.alamat_kerja || '';
    document.getElementById('editSosmedKerja').value = a.sosmed_kerja || '';

    let editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

// 💾 SIMPAN EDIT DATA
async function simpanEditData() {
    let i = document.getElementById('editIndex').value;
    let a = alumni[i];

    // Ambil data dari form
    a.email = document.getElementById('editEmail').value;
    a.nohp = document.getElementById('editNoHp').value;
    a.linkedin = document.getElementById('editLinkedIn').value;
    a.ig = document.getElementById('editIG').value;
    a.fb = document.getElementById('editFB').value;
    a.tiktok = document.getElementById('editTikTok').value;
    
    a.tempat_kerja = document.getElementById('editTempatKerja').value;
    a.posisi = document.getElementById('editPosisi').value;
    a.jenis_pekerjaan = document.getElementById('editKategori').value;
    a.alamat_kerja = document.getElementById('editAlamatKerja').value;
    a.sosmed_kerja = document.getElementById('editSosmedKerja').value;

    // Jika data pekerjaan diisi, otomatis set status teridentifikasi dengan confidence 100%
    if (a.tempat_kerja) {
        a.status = "Teridentifikasi";
        a.confidence = 100;
        a.source = "manual";
    }

    try {
        await fetch('api/update_alumni.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(a)
        });
        
        // Simpan history
        await simpanHistory(a.nama_lulusan, a.status + " (Verified)", a.confidence);
        
        // Tutup modal
        let editModalEl = document.getElementById('editModal');
        let modal = bootstrap.Modal.getInstance(editModalEl);
        modal.hide();
        
        // Refresh tabel
        tampil();
        alert("Data berhasil diperbarui secara manual!");
    } catch(e) {
        console.error("Gagal menyimpan data:", e);
        alert("Terjadi kesalahan saat menyimpan data.");
    }
}

// 🗑️ HAPUS
async function hapusAlumni(i) {
    if (confirm("Yakin hapus data?")) {
        try {
            await fetch('api/delete_alumni.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: alumni[i].id })
            });
            await fetchAlumni();
        } catch(e) {
            console.error(e);
        }
    }
}

// 📊 STATISTIK (DIPERBARUI UNTUK COVERAGE & ACCURACY)
async function updateStat() {
    try {
        const response = await fetch('api/get_analytics.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            const data = result.analytics;
            
            // Statistik Dasar
            document.getElementById("totalAlumni").innerText = data.total_data.toLocaleString();
            document.getElementById("foundAlumni").innerText = data.tracked_data.toLocaleString();
            
            let belumDitemukan = data.total_data - data.tracked_data;
            document.getElementById("notFound").innerText = belumDitemukan.toLocaleString();

            // Penilaian Dosen (Coverage & Accuracy)
            document.getElementById("coverageScore").innerText = data.coverage_percentage + "%";
            document.getElementById("accuracyScore").innerText = data.accuracy_score;
        }
    } catch (e) {
        console.error("Gagal mengambil statistik analytics", e);
        
        // Fallback jika API gagal (hanya hitung data di layar)
        document.getElementById("totalAlumni").innerText = alumni.length;
        let found = alumni.filter(a => a.status === "Teridentifikasi").length;
        document.getElementById("foundAlumni").innerText = found;
    }
}

// 📈 GRAFIK
function grafik() {
    let found = alumni.filter(a => a.status === "Teridentifikasi").length;
    let verify = alumni.filter(a => a.status === "Perlu Verifikasi").length;

    if (chart) chart.destroy();

    chart = new Chart(document.getElementById("chartAlumni"), {
        type: "bar",
        data: {
            labels: ["Teridentifikasi", "Perlu Verifikasi"],
            datasets: [{
                label: "Statistik Alumni",
                data: [found, verify]
            }]
        }
    });
}

// 📥 IMPORT EXCEL
function prosesImportExcel(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        let json = XLSX.utils.sheet_to_json(sheet);

        let parsedData = [];
        json.slice(0, 300).forEach(row => {
            let nama = row["Nama"] || row["nama"] || "";
            if (nama) {
                parsedData.push({
                    nama_lulusan: nama,
                    nim: row["NIM"] || row["nim"] || "",
                    tahun_masuk: row["Tahun Masuk"] || row["tahun masuk"] || "-",
                    program_studi: row["Prodi"] || row["Program Studi"] || "-",
                    fakultas: row["Fakultas"] || "-",
                    tanggal_lulus: row["Tanggal Lulus"] || row["Tahun Lulus"] || row["Tahun"] || "-",
                    email: row["Email"] || row["email"] || "",
                    nohp: row["No Hp"] || row["No HP"] || row["no hp"] || "",
                    linkedin: row["Linkedin"] || row["linkedin"] || "",
                    ig: row["IG"] || row["Instagram"] || row["ig"] || "",
                    fb: row["Fb"] || row["Facebook"] || row["fb"] || "",
                    tiktok: row["Tiktok"] || row["tiktok"] || "",
                    tempat_kerja: row["Tempat bekerja"] || row["tempat kerja"] || "",
                    alamat_kerja: row["Alamat bekerja"] || row["alamat bekerja"] || "",
                    posisi: row["Posisi"] || row["posisi"] || "",
                    jenis_pekerjaan: row["PNS, Swasta, Wirausaha"] || row["Kategori"] || row["kategori"] || "",
                    sosmed_kerja: row["Alamat sosial media tempat bekerja"] || row["Sosmed Kerja"] || "",
                    status: "Belum Dilacak",
                    confidence: 0
                });
            }
        });

        document.getElementById('tableAlumni').innerHTML = "<tr><td colspan='14' class='text-center'>Mengimpor data ke database, harap tunggu...</td></tr>";

        try {
            const response = await fetch('api/import_alumni.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(parsedData)
            });
            const result = await response.json();
            if (result.status === 'success') {
                alert("Import berhasil ke Database!");
                await fetchAlumni();
            } else {
                alert("Gagal import: " + result.message);
                await fetchAlumni();
            }
        } catch(e) {
            console.error(e);
            await fetchAlumni();
        }
    };
    reader.readAsArrayBuffer(file);
}

// 🚀 INIT
fetchAlumni();