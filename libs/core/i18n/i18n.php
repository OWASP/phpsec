<?php

namespace phpsec;

class i18n
{
    protected $className = 'L';

    protected $defaultLang = 'en';

    protected $forcedLang = NULL;

    protected $basePath = __DIR__;

    protected $filePath = '/lang/{LANGUAGE}.ini';

    protected $cachePath = '/cache/';

    protected $sectionSeperator = '_';

    protected $userLangs = array();

    protected $appliedLang = NULL;

    protected $langFilePath = NULL;

    protected $cacheFilePath = NULL;

    public function __construct($filePath = NULL, $cachePath = NULL, $defaultLang = NULL, $className = NULL)
    {
        $this->filePath = $this->basePath . $this->filePath;
        $this->cachePath = $this->basePath . $this->cachePath;

        if ($filePath != NULL)
            $this->filePath = $filePath;

        if ($cachePath != NULL)
            $this->cachePath = $cachePath;

        if ($defaultLang != NULL)
            $this->defaultLang = $defaultLang;

        if ($className != NULL)
            $this->className = $className;

        $this->init();
    }

    public function init()
    {
        $this->userLangs = array();

        if ($this->forcedLang != NULL)
            $this->userLangs[] = $this->forcedLang;

        if (isset($_GET['lang']) && is_string($_GET['lang']))
            $this->userLangs[] = $_GET['lang'];

        if (isset($_SESSION['lang']) && is_string($_SESSION['lang']))
            $this->userLangs[] = $_SESSION['lang'];

        $this->userLangs[] = $this->defaultLang;

        $this->userLangs = array_unique($this->userLangs);

        foreach ($this->userLangs as $key => $value)
            $this->userLangs[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', $value); // only allow a-z, A-Z and 0-9

        $this->appliedLang = NULL;

        foreach ($this->userLangs as $lang)
        {
            $this->langFilePath = str_replace('{LANGUAGE}', $lang, $this->filePath);
            if (file_exists($this->langFilePath)) {
                $this->appliedLang = $lang;
                break;
            }
        }
        if ($this->appliedLang == NULL)
            throw new \Exception('No language file was found.');

        $this->cacheFilePath = $this->cachePath . '/' . md5_file($this->langFilePath) . '_' . $this->appliedLang . '.cache.php';

        if (!file_exists($this->cacheFilePath) || filemtime($this->cacheFilePath) < filemtime($this->langFilePath))
        {
            $config = parse_ini_file($this->langFilePath, true);

            $compiled = "<?php class " . $this->className . " {\n";
            $compiled .= $this->compile($config);
            $compiled .= 'public static function __callStatic($string, $args) {' . "\n";
            $compiled .= 'vprintf(constant("self::" . $string), $args);' . "\n";
            $compiled .= "}\n}";

            file_put_contents($this->cacheFilePath, $compiled);
            chmod($this->cacheFilePath, 0644);
        }

        require_once $this->cacheFilePath;
    }

    protected function compile($config, $prefix = '')
    {
        $code = '';
        foreach ($config as $key => $value)
        {
            if (is_array($value))
                $code .= $this->compile($value, $prefix . $key . $this->sectionSeperator);
            else
                $code .= 'const ' . $prefix . $key . ' = \'' . str_replace('\'', '\\\'', $value) . "';\n";
        }
        return $code;
    }
}