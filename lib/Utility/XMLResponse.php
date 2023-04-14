<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Thomas Tanghus <thomas@tanghus.net>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\News\Utility;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Response;

/**
 * A renderer for JSON calls
 * @since 6.0.0
 */
class XMLResponse extends Response {
    /**
     * response data
     * @var string
     */
    protected $data;


    public function __construct($data, $statusCode = Http::STATUS_OK, $headers = []) {
        parent::__construct();

        $this->data = $data;
        $this->setStatus($statusCode);
        $this->addHeader('Content-Type', 'application/xml; charset=utf-8');

        foreach ($headers as $headerName => $headerValue) {
            $this->addHeader($headerName, $headerValue);
        }
    }


    public function render() {
        return $this->data;
    }
}
