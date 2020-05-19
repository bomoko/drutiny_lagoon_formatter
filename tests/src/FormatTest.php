<?php

namespace DrutinyTests\Audit;

use Drutiny\Assessment;
use Drutiny\Container;
use Drutiny\Profile\ProfileSource;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\Registry as TargetRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Drutiny\Amazee\Report\Format\Lagoon;

class FormatTest extends TestCase {

    protected $assessment;
    protected $profile;
    protected $target;

    public function __construct()
    {
        Container::setLogger(new NullLogger());
        $target = TargetRegistry::getTarget('none', '');
        $profile = ProfileSource::loadProfileByName('test');

        $policies = [];
        foreach ($profile->getAllPolicyDefinitions() as $policyDefinition) {
            $policies[] = $policyDefinition->getPolicy();
        }
        $assessment = new Assessment();
        $assessment->assessTarget($target, $policies);

        $this->profile = $profile;
        $this->assessment = $assessment;
        $this->target = $target;

        parent::__construct();
    }

    public function testJsonFormat()
    {
        $format = $this->profile->getFormatOption('lagoon');
        $output = $format->render($this->profile, $this->target, [$this->assessment]);
        $this->assertNotEmpty($json = $output->fetch());

        $object = json_decode($json);
        $this->assertTrue(is_object($object));
    }
}
