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
    }
}