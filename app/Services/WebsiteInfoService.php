<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use Exception;

/**
 * 网站信息抓取服务
 * 用于自动获取网站的标题、描述、图标等信息
 */
class WebsiteInfoService
{
    /**
     * 获取网站基本信息
     * 
     * @param string $url 网站URL
     * @return array 包含标题、描述、图标等信息的数组
     */
    public function getWebsiteInfo(string $url): array
    {
        try {
            // 确保URL格式正确
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->getDefaultInfo();
            }

            // 发送HTTP请求获取页面内容
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("无法获取网站信息: {$url}, 状态码: {$response->status()}");
                return $this->getDefaultInfo();
            }

            $html = $response->body();
            $dom = new DOMDocument();
            
            // 抑制HTML解析警告
            libxml_use_internal_errors(true);
            $dom->loadHTML($html, LIBXML_NOERROR);
            libxml_clear_errors();

            return [
                'title' => $this->extractTitle($dom),
                'description' => $this->extractDescription($dom),
                'icon' => $this->extractIcon($dom, $url),
                'success' => true
            ];

        } catch (Exception $e) {
            Log::error("获取网站信息失败: {$url}", [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            
            return $this->getDefaultInfo();
        }
    }

    /**
     * 安全地获取DOM元素的属性值
     * 
     * @param \DOMNode $node DOM节点
     * @param string $attribute 属性名
     * @return string 属性值，如果节点不是DOMElement或属性不存在则返回空字符串
     */
    private function safeGetAttribute(\DOMNode $node, string $attribute): string
    {
        if (!$node instanceof \DOMElement) {
            return '';
        }
        
        return $node->getAttribute($attribute);
    }

    /**
     * 提取网站标题
     */
    private function extractTitle(DOMDocument $dom): string
    {
        // 优先获取title标签
        $titleTags = $dom->getElementsByTagName('title');
        if ($titleTags->length > 0) {
            $title = trim($titleTags->item(0)->textContent);
            if (!empty($title)) {
                return $title;
            }
        }

        // 获取h1标签作为备选
        $h1Tags = $dom->getElementsByTagName('h1');
        if ($h1Tags->length > 0) {
            $h1 = trim($h1Tags->item(0)->textContent);
            if (!empty($h1)) {
                return $h1;
            }
        }

        return '';
    }

    /**
     * 提取网站描述
     */
    private function extractDescription(DOMDocument $dom): string
    {
        // 获取meta description
        $metaTags = $dom->getElementsByTagName('meta');
        foreach ($metaTags as $meta) {
            $name = $this->safeGetAttribute($meta, 'name');
            $property = $this->safeGetAttribute($meta, 'property');
            
            if (in_array(strtolower($name), ['description', 'desc']) || 
                in_array(strtolower($property), ['og:description', 'twitter:description'])) {
                $content = trim($this->safeGetAttribute($meta, 'content'));
                if (!empty($content)) {
                    return $content;
                }
            }
        }

        return '';
    }

    /**
     * 提取网站图标
     */
    private function extractIcon(DOMDocument $dom, string $baseUrl): string
    {
        // 获取favicon
        $iconTags = $dom->getElementsByTagName('link');
        foreach ($iconTags as $link) {
            $rel = strtolower($this->safeGetAttribute($link, 'rel'));
            if (in_array($rel, ['icon', 'shortcut icon', 'apple-touch-icon'])) {
                $href = $this->safeGetAttribute($link, 'href');
                if (!empty($href)) {
                    return $this->resolveUrl($href, $baseUrl);
                }
            }
        }

        // 尝试获取默认favicon路径
        $parsedUrl = parse_url($baseUrl);
        $defaultIcon = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/favicon.ico';
        
        // 检查默认图标是否存在
        try {
            $iconResponse = Http::timeout(5)->head($defaultIcon);
            if ($iconResponse->successful()) {
                return $defaultIcon;
            }
        } catch (Exception $e) {
            // 忽略错误，返回空字符串
        }

        return '';
    }

    /**
     * 解析相对URL为绝对URL
     */
    private function resolveUrl(string $url, string $baseUrl): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            $parsedUrl = parse_url($baseUrl);
            return $parsedUrl['scheme'] . ':' . $url;
        }

        if (strpos($url, '/') === 0) {
            $parsedUrl = parse_url($baseUrl);
            return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * 获取默认信息
     */
    private function getDefaultInfo(): array
    {
        return [
            'title' => '',
            'description' => '',
            'icon' => '',
            'success' => false
        ];
    }
} 