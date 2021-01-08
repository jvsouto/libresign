<?php

namespace OCA\Signer\Tests\Unit\Service;

use OCA\Signer\Handler\CfsslHandler;
use OCA\Signer\Service\SignatureService;
use OCA\Signer\Storage\ClientStorage;
use OCP\IConfig;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @internal
 * @coversNothing
 */
final class SignatureServiceTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IConfig
     */
    private $config;
    use ProphecyTrait;
    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    public function testGenerate()
    {
        $commonName = 'someCommonName';
        $hosts = ['someHosts'];
        $country = 'someCountry';
        $organization = 'someOrganization';
        $organizationUnit = 'someOrganizationUnit';
        $path = '/path/to/somePath';
        $password = 'somePassword';

        $content = 'someContent';

        $cfsslHandler = $this->prophesize(CfsslHandler::class);
        $clientStorage = $this->prophesize(ClientStorage::class);
        $cfsslHandler
            ->generateCertificate(
                $commonName,
                $hosts,
                $country,
                $organization,
                $organizationUnit,
                $password
            )
            ->shouldBeCalled()
            ->willReturn($content)
        ;

        $clientStorage->createFolder($path)
            ->shouldBeCalled()
        ;
        $clientStorage->saveFile($commonName.'.pfx', $content, \Prophecy\Argument::any())
            ->shouldBeCalled()
        ;

        $service = new SignatureService($cfsslHandler->reveal(), $clientStorage->reveal(), $this->config);

        $service->generate(
            $commonName,
            $hosts,
            $country,
            $organization,
            $organizationUnit,
            $path,
            $password
        );
    }
}
