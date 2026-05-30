# Product Requirements Document: Pokemon Explorer

## 1. Ringkasan

Pokemon Explorer adalah aplikasi web MVP berbasis Laravel dan Livewire untuk menampilkan data Pokemon dari PokeAPI. Fokus utama aplikasi adalah pengalaman browsing Pokemon yang cepat, elegan, responsif, dan informatif tanpa kebutuhan autentikasi maupun database lokal pada fase awal.

## 2. Tujuan Produk

- Menampilkan daftar Pokemon dari PokeAPI dengan tampilan kartu yang elegan.
- Menyediakan halaman detail untuk setiap Pokemon.
- Memberikan pengalaman pencarian dan eksplorasi yang sederhana, cepat, dan nyaman.
- Menjaga arsitektur Laravel tetap bersih, DRY, mudah diuji, dan mudah dikembangkan.
- Menghindari penggunaan auth dan database untuk MVP.

## 3. Non-Tujuan

- Tidak ada login, register, role, atau fitur user account.
- Tidak ada penyimpanan data Pokemon ke database lokal.
- Tidak ada fitur favorit, koleksi pribadi, komentar, rating, atau sharing.
- Tidak ada admin panel.
- Tidak ada integrasi API selain PokeAPI pada MVP.

## 4. Target Pengguna

- Pengguna umum yang ingin melihat daftar Pokemon dan detailnya.
- Developer atau reviewer yang ingin melihat contoh aplikasi Laravel + Livewire yang rapi dan mengikuti best practice.

## 5. Scope MVP

### 5.1 Halaman Daftar Pokemon

Halaman utama menampilkan daftar Pokemon dalam bentuk grid kartu.

Fitur utama:

- Menampilkan nama Pokemon.
- Menampilkan official artwork atau sprite.
- Menampilkan nomor Pokemon jika tersedia.
- Menampilkan type Pokemon sebagai badge berwarna.
- Menampilkan loading state saat data dimuat.
- Menampilkan empty state jika pencarian tidak menemukan hasil.
- Menampilkan error state jika PokeAPI gagal diakses.
- Mendukung pagination atau load more.
- Mendukung pencarian berdasarkan nama Pokemon.
- Kartu Pokemon dapat diklik dan mengarah ke halaman detail.

### 5.2 Halaman Detail Pokemon

Halaman detail menampilkan informasi lengkap dari satu Pokemon.

Informasi yang ditampilkan:

- Nama Pokemon.
- Nomor Pokemon.
- Official artwork atau sprite berkualitas terbaik yang tersedia.
- Type Pokemon.
- Abilities.
- Base stats: HP, Attack, Defense, Special Attack, Special Defense, Speed.
- Height.
- Weight.
- Base experience jika tersedia.
- Daftar moves terbatas, misalnya 10-20 moves pertama agar UI tetap ringkas.

State yang perlu didukung:

- Loading state.
- Error state jika Pokemon tidak ditemukan atau API gagal.
- Tombol atau link kembali ke halaman daftar.

### 5.3 Pencarian

Pencarian MVP difokuskan pada pencarian berdasarkan nama Pokemon.

Perilaku yang diharapkan:

- Pengguna dapat mengetik nama Pokemon di halaman daftar.
- Input pencarian menggunakan Livewire dengan debounce.
- Jika nama cocok, daftar menampilkan hasil relevan.
- Jika tidak ada hasil, tampilkan empty state yang jelas.

Catatan teknis:

- Karena PokeAPI tidak menyediakan endpoint pencarian kompleks, implementasi MVP dapat mengambil daftar Pokemon terbatas terlebih dahulu lalu melakukan filtering di sisi aplikasi.
- Batas awal yang direkomendasikan adalah 151 atau 300 Pokemon untuk menjaga performa dan scope tetap wajar.

## 6. UX dan Visual Design

### 6.1 Prinsip Tampilan

- Elegan, bersih, dan modern.
- Fokus pada konten Pokemon, bukan dekorasi berlebihan.
- Responsive untuk mobile, tablet, dan desktop.
- Menggunakan spacing konsisten, typography jelas, dan kontras yang baik.
- Warna type Pokemon digunakan sebagai aksen visual, bukan sebagai keseluruhan tema.

### 6.2 Halaman Daftar

Layout yang direkomendasikan:

- Header sederhana berisi nama aplikasi dan deskripsi singkat.
- Search bar yang mudah ditemukan.
- Grid kartu Pokemon responsif:
  - 1 kolom pada mobile kecil.
  - 2 kolom pada mobile besar/tablet.
  - 3-4 kolom pada desktop.
- Kartu berisi artwork, nama, nomor, dan type badges.
- Hover/focus state halus pada desktop.

### 6.3 Halaman Detail

Layout yang direkomendasikan:

- Area utama dengan artwork Pokemon sebagai fokus visual.
- Informasi dasar ditampilkan ringkas di dekat artwork.
- Stats ditampilkan sebagai bar horizontal agar mudah dipindai.
- Abilities dan moves ditampilkan dalam section terpisah.
- Navigasi kembali ke daftar terlihat jelas.

### 6.4 Aksesibilitas

- Semua gambar Pokemon memiliki alt text.
- Komponen interaktif dapat digunakan dengan keyboard.
- Warna badge tetap memiliki teks yang kontras.
- Loading dan error state memiliki teks yang jelas.

## 7. Arsitektur Teknis

### 7.1 Stack

- Laravel sebagai framework utama.
- Livewire untuk komponen interaktif.
- Blade untuk templating.
- Laravel HTTP Client untuk integrasi PokeAPI.
- Laravel Cache untuk mengurangi request berulang ke PokeAPI.
- Tailwind CSS direkomendasikan untuk styling jika tersedia dalam setup Laravel.

### 7.2 Struktur Komponen

Komponen yang direkomendasikan:

- `PokemonList`
  - Mengelola state daftar Pokemon.
  - Mengelola search query.
  - Mengelola pagination atau load more.
  - Memanggil service untuk data Pokemon.

- `PokemonDetail`
  - Mengambil detail Pokemon berdasarkan name atau id dari route.
  - Menampilkan data detail dan state error/loading.

### 7.3 Service Layer

Buat service khusus untuk komunikasi dengan PokeAPI, misalnya:

- `App\Services\PokeApiService`

Tanggung jawab service:

- Mengambil daftar Pokemon.
- Mengambil detail Pokemon berdasarkan name atau id.
- Mengambil type/detail tambahan jika diperlukan.
- Menormalisasi response API menjadi struktur data yang mudah digunakan view.
- Menangani cache.
- Menangani error dari HTTP client dengan cara yang konsisten.

Livewire component tidak boleh berisi detail endpoint PokeAPI secara langsung. Komponen hanya memanggil method service.

### 7.4 Cache

Gunakan cache Laravel untuk data dari PokeAPI.

Rekomendasi:

- Cache daftar Pokemon: 6-24 jam.
- Cache detail Pokemon: 6-24 jam.
- Cache key eksplisit, misalnya:
  - `pokeapi:pokemon:list:{limit}:{offset}`
  - `pokeapi:pokemon:detail:{nameOrId}`

Tujuan cache:

- Mengurangi latency.
- Mengurangi ketergantungan pada request berulang ke PokeAPI.
- Membuat UX lebih stabil.

### 7.5 Routing

Route publik yang direkomendasikan:

- `GET /` menampilkan daftar Pokemon.
- `GET /pokemon/{name}` menampilkan detail Pokemon.

Tidak diperlukan middleware auth.

### 7.6 Data dan Database

- Tidak menggunakan database untuk MVP.
- Tidak membuat migration atau model Pokemon lokal.
- Semua data berasal dari PokeAPI dan cache Laravel.

## 8. Integrasi PokeAPI

Base URL:

- `https://pokeapi.co/api/v2`

Endpoint yang kemungkinan digunakan:

- `GET /pokemon?limit={limit}&offset={offset}`
- `GET /pokemon/{nameOrId}`

Data list dari PokeAPI hanya menyediakan nama dan URL detail. Untuk menampilkan artwork dan type pada kartu, aplikasi dapat mengambil detail setiap Pokemon dalam batas halaman aktif.

Pertimbangan performa:

- Batasi jumlah kartu per halaman, misalnya 20.
- Gunakan cache untuk detail Pokemon.
- Hindari mengambil detail ratusan Pokemon sekaligus dalam satu request halaman.

## 9. Error Handling

Aplikasi harus menangani kondisi berikut:

- PokeAPI tidak dapat diakses.
- Response API timeout.
- Pokemon tidak ditemukan.
- Data tertentu tidak tersedia, misalnya artwork kosong.

Perilaku UI:

- Tampilkan pesan error yang ramah dan ringkas.
- Jangan tampilkan stack trace kepada user.
- Gunakan fallback sprite jika official artwork tidak tersedia.
- Gunakan placeholder visual sederhana jika semua gambar tidak tersedia.

## 10. Loading dan Empty State

Loading state:

- Skeleton card atau spinner ringan pada daftar.
- Loading indicator pada halaman detail.

Empty state:

- Ditampilkan saat pencarian tidak menghasilkan data.
- Berisi pesan jelas, misalnya "Pokemon tidak ditemukan."

## 11. Best Practice Laravel

Prinsip implementasi:

- Pisahkan integrasi API ke service class.
- Hindari duplikasi logic mapping response API.
- Gunakan config untuk base URL PokeAPI jika diperlukan.
- Gunakan dependency injection untuk service.
- Gunakan Laravel HTTP fake dalam testing.
- Gunakan named routes.
- Jaga Livewire component tetap fokus pada state dan interaksi UI.
- Jangan menaruh business logic besar di Blade.
- Gunakan partial Blade/component kecil untuk elemen yang berulang seperti badge type dan stat bar.

## 12. Testing

Testing MVP minimal:

- Service test untuk `PokeApiService` menggunakan HTTP fake.
- Test daftar Pokemon berhasil dimuat.
- Test detail Pokemon berhasil dimuat.
- Test API error ditangani dengan benar.
- Livewire test untuk pencarian.
- Livewire test untuk render halaman detail berdasarkan route parameter.

Acceptance test manual:

- User membuka `/` dan melihat daftar Pokemon.
- User mencari Pokemon berdasarkan nama.
- User klik salah satu kartu dan masuk ke halaman detail.
- User melihat stats, abilities, type, height, weight, dan moves.
- User dapat kembali ke halaman daftar.
- Aplikasi tetap menampilkan pesan yang baik saat API gagal.

## 13. Kriteria Penerimaan

MVP dianggap selesai jika:

- Halaman daftar Pokemon dapat diakses tanpa login.
- Daftar Pokemon tampil dalam grid kartu responsif.
- Setiap kartu menampilkan nama, gambar, nomor, dan type.
- Pencarian berdasarkan nama berjalan.
- Pagination atau load more tersedia.
- Halaman detail Pokemon tersedia.
- Halaman detail menampilkan informasi utama Pokemon.
- Aplikasi tidak menggunakan database untuk menyimpan data Pokemon.
- Aplikasi tidak memiliki auth.
- Request ke PokeAPI dilakukan melalui service layer.
- Data dari PokeAPI menggunakan cache Laravel.
- Loading, empty, dan error state tersedia.
- Test utama untuk service dan Livewire component tersedia.

## 14. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| PokeAPI lambat atau gagal | User tidak bisa melihat data | Gunakan cache dan error state yang jelas |
| Terlalu banyak request detail Pokemon | Halaman list lambat | Batasi item per halaman dan cache detail |
| Livewire component menjadi terlalu besar | Sulit dirawat | Pindahkan logic API dan mapping ke service |
| UI terlalu ramai | Pengalaman kurang elegan | Gunakan layout bersih dan aksen warna secukupnya |
| Pencarian global mahal | Performa turun | Batasi dataset awal atau implementasikan pencarian langsung detail by name |

## 15. Rekomendasi Iterasi Setelah MVP

Fitur yang dapat dipertimbangkan setelah MVP:

- Filter berdasarkan type.
- Sorting berdasarkan nomor atau nama.
- Favorit Pokemon menggunakan database.
- Perbandingan stats antar Pokemon.
- Evolutions chain.
- Detail species, habitat, generation, dan flavor text.
- Dark mode.
- SEO metadata untuk halaman detail.

