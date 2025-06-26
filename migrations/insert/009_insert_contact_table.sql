INSERT INTO contacts (
    company_id, organization_id, name, title,
    mail, phone, address, status, created_at
) VALUES

-- Ankara Teknoloji A.Ş. contacts (company_id: 1)
(1, 1, 'Ali Kaya', 'Genel Müdür', 'ali.kaya@ankarateknoloji.com', '0312-555-0001', 'Teknokent Mahallesi, Silikon Vadisi No:1', 'active', NOW()),
(1, 1, 'Zehra Şen', 'IT Müdürü', 'zehra.sen@ankarateknoloji.com', '0312-555-0001', 'Teknokent Mahallesi, Silikon Vadisi No:1', 'active', NOW()),
(1, 1, 'Murat Çelik', 'Satın Alma Uzmanı', 'murat.celik@ankarateknoloji.com', '0312-555-0001', 'Teknokent Mahallesi, Silikon Vadisi No:1', 'active', NOW()),

-- Mehmet Yılmaz Danışmanlık contacts (company_id: 2)
(2, 1, 'Mehmet Yılmaz', 'Danışman', 'mehmet.yilmaz@email.com', '0312-555-0002', 'Çankaya Mahallesi No:15', 'active', NOW()),

-- İstanbul Yazılım Ltd. contacts (company_id: 3)
(3, 1, 'Elif Yılmaz', 'Proje Müdürü', 'elif.yilmaz@istanbulyazilim.com', '0212-555-0003', 'Maslak Mahallesi, İş Merkezi No:45', 'active', NOW()),
(3, 1, 'Serkan Özdemir', 'Teknik Direktör', 'serkan.ozdemir@istanbulyazilim.com', '0212-555-0003', 'Maslak Mahallesi, İş Merkezi No:45', 'active', NOW()),

-- Veri Analiz Merkezi A.Ş. contacts (company_id: 4)
(4, 2, 'Dr. Fatma Arslan', 'Genel Müdür', 'fatma.arslan@verianaliz.com', '0312-555-0004', 'Bilkent Mahallesi, Analiz Plaza No:23', 'active', NOW()),
(4, 2, 'Ahmet Koç', 'Veri Bilimci', 'ahmet.koc@verianaliz.com', '0312-555-0004', 'Bilkent Mahallesi, Analiz Plaza No:23', 'active', NOW()),

-- Bursa İmalat Sanayi A.Ş. contacts (company_id: 5)
(5, 2, 'Selin Arslan', 'Satın Alma Müdürü', 'selin.arslan@bursaimalat.com', '0224-555-0005', 'Organize Sanayi Bölgesi, 15. Cadde No:78', 'active', NOW()),

-- Ayşe Demir Eğitim contacts (company_id: 6)
(6, 2, 'Ayşe Demir', 'Eğitim Uzmanı', 'ayse.demir@email.com', '0312-555-0006', 'Kızılay Mahallesi No:89', 'active', NOW()),

-- Bulut Teknolojileri A.Ş. contacts (company_id: 7)
(7, 3, 'Ömer Demir', 'CTO', 'omer.demir@buluttek.com', '0212-555-0007', 'Levent Mahallesi, Bulut Plaza No:12', 'active', NOW()),
(7, 3, 'Seda Avcı', 'Altyapı Müdürü', 'seda.avci@buluttek.com', '0212-555-0007', 'Levent Mahallesi, Bulut Plaza No:12', 'active', NOW()),
(7, 3, 'Kemal Öztürk', 'Satış Müdürü', 'kemal.ozturk@buluttek.com', '0212-555-0007', 'Levent Mahallesi, Bulut Plaza No:12', 'active', NOW()),

-- Antalya Turizm Ltd. contacts (company_id: 8)
(8, 3, 'Serkan Özer', 'Operasyon Müdürü', 'serkan.ozer@antalyaturizm.com', '0242-555-0008', 'Lara Mahallesi, Turizm Caddesi No:56', 'active', NOW()),
(8, 3, 'Pınar Aydın', 'Rezervasyon Uzmanı', 'pinar.aydin@antalyaturizm.com', '0242-555-0008', 'Lara Mahallesi, Turizm Caddesi No:56', 'active', NOW()),

-- Can Özkan Danışmanlık contacts (company_id: 9)
(9, 3, 'Can Özkan', 'Danışman', 'can.ozkan@consulting.com', '0312-555-0009', 'Bahçelievler Mahallesi No:34', 'active', NOW());