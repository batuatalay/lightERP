<?php
/**
 * Slug Helper Class
 * Organization name'leri slug'a çevirmek için
 */
class SlugHelper {
    
    /**
     * @param string $text
     * @param string $separator
     * @return string
     */
    public static function generate($text, $separator = '-') {
        // Türkçe karakterleri dönüştür
        $turkishChars = [
            'ş' => 's', 'Ş' => 's',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
            'ı' => 'i', 'İ' => 'i'
        ];
        
        $text = strtr($text, $turkishChars);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Özel karakterleri ve boşlukları temizle
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        
        // Birden fazla boşluk/tire'yi tek tire'ye çevir
        $text = preg_replace('/[\s-]+/', $separator, $text);
        
        // Başındaki ve sonundaki tire'leri temizle
        $text = trim($text, $separator);
        
        return $text;
    }
    
    /**
     * @param string $text
     * @return string
     */
    public static function generateUnique($text) {
        $baseSlug = self::generate($text);
        
        // Benzersiz slug için timestamp ekle
        return $baseSlug . '-' . time();
    }
    
    /**
     * @param string $text
     * @param int $maxLength
     * @return string
     */
    public static function generateWithLimit($text, $maxLength = 50) {
        $slug = self::generate($text);
        
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            // Son tire'yi temizle
            $slug = rtrim($slug, '-');
        }
        
        return $slug;
    }
}

// Kullanım örnekleri:
/*
echo SlugHelper::generate('Batu Tech'); // batu-tech
echo SlugHelper::generate('Özel Şirket A.Ş.'); // ozel-sirket-as
echo SlugHelper::generate('My Company & Co.'); // my-company-co
echo SlugHelper::generateUnique('Batu Tech'); // batu-tech-1720537200
echo SlugHelper::generateWithLimit('Very Long Organization Name Ltd.', 20); // very-long-organizat
*/