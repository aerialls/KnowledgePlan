<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Cache;

class FilesystemCache implements CacheInterface
{
    private $dir;

    /**
     * Constructor.
     *
     * @param string $dir The directory.
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return file_exists($this->dir.'/'.md5($key).'.php');
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $file = $this->dir.'/'.md5($key).'.php';

        return file_exists($file) ? require($file) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (!is_dir($this->dir) && false === @mkdir($this->dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create the "%s" directory.', $this->dir));
        }

        $file = $this->dir.'/'.md5($key).'.php';
        $valueExport = var_export($value, true);
        $content = <<<EOF
<?php

return $valueExport;
EOF;

        if (false === @file_put_contents($file, $content, LOCK_EX)) {
            throw new \RuntimeException(sprintf('Unable to write the "%s" file.', $file));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $file = $this->dir.'/'.md5($key).'.php';
        if (file_exists($file) && false === @unlink($file)) {
            throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (is_dir($this->dir)) {
            foreach (new \DirectoryIterator($this->dir) as $file) {
                if ($file->isFile() && false === @unlink($file->getRealPath())) {
                    throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file->getRealPath()));
                }
            }
        }
    }
}