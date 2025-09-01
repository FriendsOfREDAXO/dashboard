<?php

/**
 * @package yakamara\dashboard
 */
abstract class rex_dashboard_item
{
    use rex_factory_trait;

    const ATTRIBUTES = [
        'width' => 'gs-w',
        'height' => 'gs-h',
        'x' => 'gs-x',
        'y' => 'gs-y',
        'active' => 'data-active',
    ];

    private static $ids = [];
    private static $itemData = null;
    private static $jsFiles = [];
    private static $cssFiles = [];

    protected $name = null;
    protected $id = null;
    protected $content = '';
    protected $options = [
        'show-header' => true,
    ];
    protected $attributes = [
        'gs-w' => 1,
        'gs-h' => 3,
        'gs-no-resize' => 1,
    ];
    protected $useCache = true;

    abstract protected function getData();

    protected function __construct($id, $name)
    {
        $this->id = rex_string::normalize($id);

        if (in_array($this->id, self::$ids)) {
            throw new Exception('ID "' . $id . '" (normalized: "' . $this->id . '") is already in use.');
        }

        self::$ids[] = $this->id;

        $this->name = $name;

        /** get stored positions and dimensions of item @see rex_api_dashboard_store */
        if ($user = rex::getUser()) {
            if (is_null(self::$itemData)) {
                self::$itemData = rex_config::get('dashboard', 'items_'.$user->getId(), []);
            }

            if (array_key_exists($this->id, self::$itemData)) {
                foreach (static::ATTRIBUTES as $attribute) {
                    if (array_key_exists($attribute, self::$itemData[$this->id])) {
                        $this->setAttribute($attribute, self::$itemData[$this->id][$attribute]);
                    }
                }
            }
        }
    }

    public static function factory($id, $name) : rex_dashboard_item
    {
        $class = self::getFactoryClass();
        return new $class($id, $name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setColumns(int $colCount)
    {
        $this->attributes['gs-w'] = max(0, min(3, $colCount));
        return $this;
    }

    public function getContent($refresh = false)
    {
        if ($this->useCache) {
            $cacheFile = rex_addon::get('dashboard')->getCachePath($this->getId() . '.data');
            if (file_exists($cacheFile) AND !$refresh) {
                return rex_file::getCache($cacheFile);
            }
            else {
                $data = $this->getData();
                rex_file::putCache($cacheFile, $data);
                return $data;
            }
        }

        return $this->getData();
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getAttributes()
    {
        $this->attributes['data-id'] = $this->getId();
        return $this->attributes;
    }

    public function addJs($filename, $name = null)
    {
        if (file_exists($filename)) {
            if (is_null($name)) {
                $name = basename($filename);
            }

            if (!array_key_exists($name, self::$jsFiles)) {
                self::$jsFiles[$name] = $filename;
            }
        }

        return $this;
    }

    public function isActive($userId = null)
    {
        if (is_null($userId)) {
            if ($user = rex::getUser()) {
                $userId = $user->getId();
            }
        }
        else if ($userId instanceof rex_user) {
            $userId = $userId->getId();
        }
        else {
            $userId = intval($userId);
        }

        if (empty($userId)) {
            return false;
        }

        return (bool) (self::$itemData[$this->id]['data-active'] ?? false);
    }

    public static function getJsFiles()
    {
        return self::$jsFiles;
    }

    public function addCss($filename, $name = null)
    {
        if (file_exists($filename)) {
            if (is_null($name)) {
                $name = basename($filename);
            }

            if (!array_key_exists($name, self::$cssFiles)) {
                self::$cssFiles[$name] = $filename;
            }
        }

        return $this;
    }

    public static function getCssFiles()
    {
        return self::$cssFiles;
    }

    public function useCache($useCache = true)
    {
        $this->useCache = $useCache;
        return $this;
    }

    public function isCached()
    {
        return $this->useCache;
    }

    public function getCacheDate()
    {
        if (file_exists($cacheFile = rex_addon::get('dashboard')->getCachePath($this->getId() . '.data'))) {
            if (function_exists('filemtime')) {
                $datetime = new DateTime();
                return $datetime->setTimestamp(filemtime($cacheFile));
            }
        }

        return new DateTime();
    }
}
