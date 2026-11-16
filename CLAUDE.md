# Ev Intra

Evin ortak paneli (arayüz ALMANCA): Einkaufsliste, Putzplan, Raumbuchung, Haushaltskasse, Kalender.
Sahibi: Yağız. Amaç: yazılım öğrenmek — Claude kodu yazmadan önce anlatır.

## Yığın
Laravel (güncel) · Blade + Tailwind · SQLite (geliştirme) · Pest · Vite

## Ortam
- Herd yok; MAMP'in PHP 8.4'ü kullanılıyor. Terminalde önce: `export PATH="/Applications/MAMP/bin/php/php8.4.27/bin:/Applications/MAMP/bin/php:$PATH"`
- Proje: `/Applications/MAMP/htdocs/intra-home` · adres: http://localhost:8000
- Starter kit: Livewire + Volt (Flux). `config/livewire.php` → `component_layout` = `components.layouts.app` (Livewire 4 uyumu için).

## Komutlar
- Çalıştır: `composer run dev`
- Test: `php artisan test`
- Veritabanı sıfırla: `php artisan migrate:fresh --seed`

## Kurallar
- Arayüz metinleri ALMANCA (du-Form, kısa: „Speichern“, „Löschen“, „Erledigt“). Metinler `lang/de/*.php` ya da `__('...')` ile, Blade'e gömülü değil.
- Yerel ayar: `APP_LOCALE=de`, `APP_TIMEZONE=Europe/Berlin`, tarih `d.m.Y`, saat `H:i`, para `12,50 €` (Number::currency($cents/100, 'EUR', 'de')).
- Doğrulama mesajları Almanca: laravel-lang paketi.
- Kod yorumları Türkçe. Kod (tablo, model, değişken, route) İngilizce: Laravel çoğullaması İngilizce çalışır (ShoppingItem → shopping_items).
- Para kuruş (cent) cinsinden tamsayı: `amount_cents`. Ondalık/float yok.
- Her modül: migration → model → FormRequest → resource controller → policy → Blade → test.
- Başkasının kaydını düzenleme yetkisi policy ile kontrol edilir.
- Roller: `App\Enums\Role` (eltern/kind/gast). Gast salt okur: her policy'nin create/update/delete'i `$user->canWrite()` ister. Üye yönetimi yalnız eltern (`UserPolicy`).
- Açık kayıt yalnız ilk kullanıcı için (o eltern olur); sonrakileri eltern /members'tan ekler.
- Bir modül = bir git branch'i (`modul/shopping-list`). Bitince `kod-inceleyici` ajanı, sonra merge.

## Claude için
- Yağız "sen yaz" demedikçe tam kod yazma; yaklaşımı ve ipucunu ver.
- vendor/, node_modules/, storage/logs içeriğini okuma (gerekirse log son 60 satır).
- Cevaplar kısa olsun.

## Durum
Bitmiş modüller: Ev üyeleri ve giriş, Einkaufsliste
Açık iş: lang/de.json içinde laravel-lang metinleri hâlâ Sie-Form; du-Form yapılacak.
Sıradaki: Putzplan
