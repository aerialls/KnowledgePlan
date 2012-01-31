<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Symfony;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;

class GzipStreamedResponse extends StreamedResponse
{
    private $gzip = false;

    public function prepare(Request $request)
    {
        parent::prepare($request);

        $encoding = $request->headers->get('Accept-Encoding');
        if (false !== strpos($encoding, 'gzip')) {
            $this->headers->set('Content-Encoding', 'gzip');
            $this->gzip = true;
        }
    }

    public function sendContent()
    {
        if (true === $this->gzip) {
            ob_start('ob_gzhandler');
        }

        parent::sendContent();
    }
}

