<?php

namespace Stoffer\Event;

use Gnugat\Redaktilo\File;
use Zend\EventManager\Event;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class RequestFileReview extends Event
{
    const EVENT = 'file_review_requested';

    /** @var File */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;

        parent::__construct(self::EVENT, 'stoffer');
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFilename()
    {
        return $this->file->getFilename();
    }
}
