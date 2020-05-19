<?php

namespace Drutiny\Amazee\Report\Format;

use Drutiny\Profile;
use Drutiny\Report\Format;
use Drutiny\Target\Target;
use Drutiny\Report\Format\JSON;
use Drutiny\Assessment;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class Lagoon extends JSON
{

    /**
     * The output location or method.
     *
     * @var string
     */
    protected $output;

    protected $endpointUrl = "https://14fe9f9a-ae04-4d75-b37e-f23eceb0050d.mock.pstmn.io";

    public function __construct($options)
    {
        parent::__construct($options);
        $this->setFormat('lagoon');
    }

    protected function preprocessResult(
      Profile $profile,
      Target $target,
      Assessment $assessment
    ) {
        $schema = parent::preprocessResult($profile, $target, $assessment);
        return $schema;
    }

    protected function sendToLagoon($variables)
    {
        $client = new Client();
        $res = $client->request('POST', $this->endpointUrl, [
          'json' => $variables,
        ]);
    }

    protected function renderResult(array $variables)
    {
        $this->sendToLagoon($variables);
        return json_encode($variables);
    }

}

?>
