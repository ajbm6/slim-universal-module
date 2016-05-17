<?php
namespace TheCodingMachine;


use Simplex\Container;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;
use Slim\Interfaces\Http\EnvironmentInterface;

class SlimServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    private function getRequest(string $url) : Request
    {
        $fpt = fopen("php://stdin", 'r');
        return new Request('GET', Uri::createFromString($url) , new Headers() , [] , [], new Stream($fpt));
    }

    public function testProvider()
    {
        $simplex = new Container();
        $simplex->register(new SlimServiceProvider());

        $app = $simplex->get(App::class);
        $app->post('/foo', function() {

        });

        $this->assertInstanceOf(App::class, $app);
        /* @var $app App */
        $this->assertInstanceOf(EnvironmentInterface::class, $app->getContainer()->get('environment'));
        $this->assertInstanceOf(Request::class, $app->getContainer()->get('request'));
        $this->assertInstanceOf(Response::class, $app->getContainer()->get('response'));


        // Test 404
        $request = $this->getRequest('/toto');
        $response = new Response();
        $response = $app($request, $response);
        $this->assertEquals(404, $response->getStatusCode() );

        // Test 405
        $request = $this->getRequest('/foo');
        $response = new Response();
        $response = $app($request, $response);
        $this->assertEquals(405, $response->getStatusCode() );



    }

    public function testErrorHandling()
    {
        $simplex = new Container();
        $simplex->register(new SlimServiceProvider());
        $simplex['request'] = $this->getRequest('/triggerException');
        $simplex['response'] = new Response();

        $app = $simplex->get(App::class);
        $app->get('/triggerException', function(Request $request, Response $response) {
            throw new \Exception('boom');
        });

        $response = $app->run(true);
        $this->assertEquals(500, $response->getStatusCode() );
    }

    public function testPhpErrorHandling()
    {
        $simplex = new Container();
        $simplex->register(new SlimServiceProvider());
        $simplex['request'] = $this->getRequest('/triggerError');
        $simplex['response'] = new Response();

        $app = $simplex->get(App::class);
        $app->get('/triggerError', function(Request $request, Response $response) {
            $t->toto();
        });

        $response = $app->run(true);
        $this->assertEquals(500, $response->getStatusCode() );
    }
}
