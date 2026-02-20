<?php
/*
Plugin Name: JapaneseLanguageAutoTags
Version: 0.1.0
Description: Yahoo!APIを使用して文章内の単語などを自動抽出しタグ化します
Author: taoka toshiaki
Author URI: https://taoka-toshiaki.com/
Plugin URI: https://wordpress.org/extend/plugins/JapaneseLanguageAutoTags/
*/
if (! defined('ABSPATH')) {
    exit;
}

class JapaneseLanguageAutoTags
{
    protected $endpoint = 'https://jlp.yahooapis.jp/KeyphraseService/V2/extract';
    public $db_option = 'JapaneseLanguageAutoTags';

    public function addMenu()
    {
        add_menu_page(
            'JapaneseLanguageAutoTags',
            'JapaneseLanguageAutoTags',
            'manage_options',
            __FILE__,
            array($this, 'renderFrontPage'),
            '',
            8,
        );
    }

    public function renderFrontPage()
    {
        wp_enqueue_script(
            'tailwind-cdn',
            'https://cdn.tailwindcss.com',
            array(),
            null,
            true
        );
        wp_enqueue_style(
            'googleapis',
            'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap',
            array(),
            null,
            true
        );

        $ApiKey = '';
        $options = get_option($this->db_option);
        if (!empty($options)) {
            $ApiKey = $options['ApiKey'];
        }

        include_once dirname(__FILE__) . '/Page.php';
    }

    public function addCredit()
    {
        echo '<center><a href="https://developer.yahoo.co.jp/sitemap/"
         rel="nofollow" target="_blank">Webサービス by Yahoo! JAPAN</a></center>';
    }

    public function setJapaneseLanguageAutoTags()
    {
        $options = [];
        $ApiKey = strip_tags($_POST['APIKEY']);
        $options['ApiKey'] = $ApiKey;
        update_option($this->db_option, $options);
        die(0);
    }

    public function saveTags($postId)
    {
        $tags = null;
        $post = get_post($postId);
        
        $content = $this->remove_code_blocks($post->post_content);
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content, true);
        $content = strip_tags($content);
        $patterns = [
            '#https?://(www\.)?(youtube\.com|youtu\.be)/[^\s]+#i',
            '#https?://(www\.)?twitter\.com/[^\s]+#i',
            '#https?://(www\.)?x\.com/[^\s]+#i',
            '#https?://(www\.)?instagram\.com/[^\s]+#i',
            '#https?://(www\.)?tiktok\.com/[^\s]+#i',
        ];
        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        $content = $this->trimUtf8ByBytes($content);

        $options = get_option($this->db_option);
        if (!empty($options)) {
            $ApiKey = $options['ApiKey'];
        }

        if (isset($ApiKey)) {
            $headers = [
                'Content-Type: application/json',
                'User-Agent: Yahoo AppID: ' . $ApiKey,
            ];
            $param = [
                'id' => time(),
                'jsonrpc' => '2.0',
                'method' => 'jlp.keyphraseservice.extract',
                'params' => [
                    'q' => $content
                ]
            ];

            try {
                $curl = curl_init($this->endpoint);
                curl_setopt($curl, CURLOPT_POST, TRUE);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($param, JSON_UNESCAPED_UNICODE));
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

                $response = json_decode(curl_exec($curl));
                if (json_last_error() === JSON_ERROR_NONE) {
                    if ($response?->result?->phrases) {
                        foreach ($response->result->phrases as $keys => $word) {
                            if ($word->text) {
                                $tags[] = $word->text;
                            }
                            if (is_array($tags)) {
                                wp_set_post_tags($postId, implode(',', array_unique($tags)), false);
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                //echo $th->getMessage();
            }
        }
    }

    public function remove_code_blocks($content)
    {
        $blocks = parse_blocks($content);

        $filtered = array_filter($blocks, function ($block) {

            return !(
                str_contains($block['blockName'] ?? '', 'code') ||
                str_contains($block['blockName'] ?? '', 'syntax')
            );
        });

        return serialize_blocks($filtered);
    }

    public function trimUtf8ByBytes(string $text, int $maxBytes = 3584): string
    {
        $result = '';
        $bytes  = 0;
        $len    = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $charBytes = strlen($char);

            if (($bytes + $charBytes) > $maxBytes) {
                break;
            }

            $result .= $char;
            $bytes  += $charBytes;
        }

        return $result;
    }
}

$japaneseLanguageAutoTags = new JapaneseLanguageAutoTags();
add_action('admin_menu', array($japaneseLanguageAutoTags, 'addMenu'));
add_action('wp_footer', array($japaneseLanguageAutoTags, 'addCredit'));
add_action('wp_ajax_setJapaneseLanguageAutoTags', array($japaneseLanguageAutoTags, 'setJapaneseLanguageAutoTags'));
add_action('save_post', array($japaneseLanguageAutoTags, 'saveTags'), 99);
add_action('publish_post', array($japaneseLanguageAutoTags, 'saveTags'), 99);
