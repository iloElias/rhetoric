<?php

use Ilias\Rhetoric\Exceptions\MiddlewareException;
use Ilias\Rhetoric\Middleware\IMiddleware;
use PHPUnit\Framework\TestCase;
use Ilias\Rhetoric\Router\Router;
use Ilias\Rhetoric\Router\Route;
use Ilias\Rhetoric\Router\RouterGroup;
use PHPUnit\Framework\MockObject\MockObject;

class TestController
{
	public static $executed = false;

	public static function reset()
	{
		self::$executed = false;
	}

	public static function index()
	{
		self::$executed = true;
	}

	public function testMethod()
	{
		self::$executed = true;
	}
}

class SampleMiddleware implements IMiddleware
{
	public static $executed = false;

	public static function reset()
	{
		self::$executed = false;
	}

	public static function handle()
	{
		self::$executed = true;
	}
}

class FirstMiddleware implements IMiddleware
{
	public static $executed = false;

	public static function reset()
	{
		self::$executed = false;
	}

	public static function handle()
	{
		self::$executed = true;
	}
}

class SecondMiddleware implements IMiddleware
{
	public static $executed = false;

	public static function reset()
	{
		self::$executed = false;
	}

	public static function handle()
	{
		self::$executed = true;
	}
}

class FailingMiddleware implements IMiddleware
{
	public static function handle()
	{
		throw new MiddlewareException();
	}
}

class RouterTest extends TestCase
{
	/**
	 * @var MockObject|Router
	 */
	private $routerMock;

	protected function setUp(): void
	{
		$this->routerMock = $this->getMockBuilder(Router::class)
			->disableOriginalConstructor()
			->onlyMethods(['dispatch'])
			->getMock();

		$_SERVER["DOCUMENT_ROOT"] = __DIR__;
		$_SERVER["REQUEST_METHOD"] = 'GET';
		$_SERVER["REQUEST_URI"] = '/test';

		// Reset execution flags
		TestController::reset();
		SampleMiddleware::reset();
		FirstMiddleware::reset();
		SecondMiddleware::reset();
	}

	protected function tearDown(): void
	{
		// Reset Router static properties
		$reflection = new \ReflectionClass(Router::class);
		$routesProperty = $reflection->getProperty('routes');
		$routesProperty->setAccessible(true);
		$routesProperty->setValue(null, []);

		$paramsProperty = $reflection->getProperty('params');
		$paramsProperty->setAccessible(true);
		$paramsProperty->setValue(null, []);
	}


	public function testAddGetRoute()
	{
		Router::get('/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testAddPostRoute()
	{
		Router::post('/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testAddPutRoute()
	{
		Router::put('/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testAddPatchRoute()
	{
		Router::patch('/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testAddDeleteRoute()
	{
		Router::delete('/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testAddCustomRoute()
	{
		Router::custom('OPTIONS', '/test', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/test', $routes);
	}

	public function testRouteGroup()
	{
		Router::group('/api', function ($group) {
			$group->get('/users', 'TestController@index');
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/users', $routes);
	}

	public function testNestedRouteGroup()
	{
		Router::group('/api', function ($group) {
			$group->group('/v1', function ($group) {
				$group->get('/users', 'TestController@index');
			});
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/v1/users', $routes);
	}

	public function testHandleRoute()
	{
		Router::get('/test', 'TestController@testMethod');
		$route = new Route('GET', '/test', 'TestController@testMethod');
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
		$this->assertTrue(TestController::$executed);
	}

	public function testMiddlewareExecution()
	{
		Router::get('/test', 'TestController@testMethod', [SampleMiddleware::class]);

		$route = new Route('GET', '/test', 'TestController@testMethod', [SampleMiddleware::class]);
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
		$this->assertTrue(SampleMiddleware::$executed);
		$this->assertTrue(TestController::$executed);
	}

	public function testMiddlewareOrder()
	{
		Router::get('/test', 'TestController@testMethod', [FirstMiddleware::class, SecondMiddleware::class]);

		$route = new Route('GET', '/test', 'TestController@testMethod', [FirstMiddleware::class, SecondMiddleware::class]);
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
		$this->assertTrue(FirstMiddleware::$executed);
		$this->assertTrue(SecondMiddleware::$executed);
		$this->assertTrue(TestController::$executed);
	}

	public function testSingleRouteInGroup()
	{
		Router::group('/api', function ($group) {
			$group->get('/test', 'TestController@testMethod');
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/test', $routes);
	}

	public function testMultipleRoutesInGroup()
	{
		Router::group('/api', function ($group) {
			$group->get('/test1', 'TestController@testMethod');
			$group->post('/test2', 'TestController@testMethod');
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/test1', $routes);
		$this->assertContains('/api/test2', $routes);
	}

	public function testNestedGroups()
	{
		Router::group('/api', function ($group) {
			$group->group('/v1', function ($group) {
				$group->get('/users', 'TestController@index');
			});
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/v1/users', $routes);
	}

	public function testMiddlewareInGroup()
	{
		Router::group('/api', function ($group) {
			$group->get('/test', 'TestController@testMethod', [SampleMiddleware::class]);
		});
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/test', $routes);

		$route = new Route('GET', '/api/test', 'TestController@testMethod', [SampleMiddleware::class]);
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
		$this->assertTrue(SampleMiddleware::$executed);
		$this->assertTrue(TestController::$executed);
	}

	public function testNestedGroupsWithMiddleware()
	{
		Router::group('/api', function ($group) {
			$group->group('/v1', function ($group) {
				$group->get('/users', 'TestController@index', [SampleMiddleware::class]);
			});
		}, [FirstMiddleware::class]);
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/api/v1/users', $routes);

		$route = new Route('GET', '/api/v1/users', 'TestController@index', [FirstMiddleware::class, SampleMiddleware::class]);
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
		$this->assertTrue(FirstMiddleware::$executed);
		$this->assertTrue(SampleMiddleware::$executed);
		$this->assertTrue(TestController::$executed);
	}

	public function testRouteNotFoundException()
	{
		$this->expectException(\Ilias\Rhetoric\Exceptions\RouteNotFoundException::class);
		Router::dispatch('GET', '/nonexistent');
	}

	public function testMethodNotAllowedException()
	{
		Router::get('/test/not-allowed', 'TestController@testMethod');

		$this->expectException(\Ilias\Rhetoric\Exceptions\MethodNotAllowedException::class);
		Router::dispatch('POST', '/test/not-allowed');
	}

	public function testSingleParamInRoute()
	{
		Router::get('/user/{id}', 'TestController@testMethod');
		Router::dispatch('GET', '/user/123');
		$this->assertEquals(['id' => '123'], $this->invokeProperty(Router::class, 'params'));
	}

	public function testMultipleParamsInRoute()
	{
		Router::get('/user/{id}/post/{postId}', 'TestController@testMethod');
		Router::dispatch('GET', '/user/123/post/456');
		$this->assertEquals(['id' => '123', 'postId' => '456'], $this->invokeProperty(Router::class, 'params'));
	}

	public function testManyParamsInRoute()
	{
		$params = ['id', 'postId', 'commentId', 'replyId'];
		$route = '/user/{id}/post/{postId}/comment/{commentId}/reply/{replyId}';
		$uri = '/user/1/post/2/comment/3/reply/4';
		$expected = array_combine($params, ['1', '2', '3', '4']);

		Router::get($route, 'TestController@testMethod');
		Router::dispatch('GET', $uri);
		$this->assertEquals($expected, $this->invokeProperty(Router::class, 'params'));
	}

	public function testDuplicateParamInRoute()
	{
		$this->expectException(\Ilias\Rhetoric\Exceptions\DuplicateParameterException::class);
		Router::get('/user/{id}/post/{id}', 'TestController@testMethod');
	}

	public function testDuplicateRoute()
	{
		Router::get('/user/{id}', 'TestController@testMethod');
		$this->expectException(\Ilias\Rhetoric\Exceptions\DuplicateRouteException::class);
		Router::get('/user/{id}', 'TestController@testMethod');
	}

	public function testInvalidRouteDefinition()
	{
		$this->expectException(\Ilias\Rhetoric\Exceptions\RouteNotFoundException::class);
		Router::get('/invalid/[pattern]', 'TestController@testMethod');
		Router::dispatch('GET', '/invalid/pattern');
	}

	public function testMiddlewareFailureHandling()
	{
		Router::get('/test', 'TestController@testMethod', [FailingMiddleware::class]);
		$route = new Route('GET', '/test', 'TestController@testMethod', [FailingMiddleware::class]);

		$this->expectException(\Ilias\Rhetoric\Exceptions\MiddlewareException::class);
		$this->invokeMethod(Router::class, 'handleRoute', [$route]);
	}

	public function testRouteOrder()
	{
		Router::get('/user/{id}', 'TestController@testMethod');
		Router::get('/user/profile', 'TestController@index');

		Router::dispatch('GET', '/user/profile');
		$this->assertTrue(TestController::$executed);

		Router::dispatch('GET', '/user/123');
		$this->assertEquals(['id' => '123'], $this->invokeProperty(Router::class, 'params'));
	}

	public function testEmptyRouteGroup()
	{
		Router::group('/api', function ($group) {
			// No routes added
		});
		$routes = Router::getRoutesAvailable();
		$this->assertEmpty($routes);
	}

	public function testCustomHttpMethod()
	{
		Router::custom('TRACE', '/trace', 'TestController@testMethod');
		$routes = Router::getRoutesAvailable();
		$this->assertContains('/trace', $routes);
	}

	public function testParameterTypeChecking()
	{
		Router::get('/user/{id}', 'TestController@testMethod');
		Router::dispatch('GET', '/user/123');
		$params = $this->invokeProperty(Router::class, 'params');
		$this->assertIsString($params['id']);

		Router::dispatch('GET', '/user/abc123');
		$params = $this->invokeProperty(Router::class, 'params');
		$this->assertIsString($params['id']);
	}

	protected function invokeProperty($class, $propertyName)
	{
		$reflection = new \ReflectionClass($class);
		$property = $reflection->getProperty($propertyName);
		$property->setAccessible(true);
		return $property->getValue();
	}

	protected function invokeMethod($class, $methodName, array $parameters = [])
	{
		$reflection = new \ReflectionClass($class);
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs(null, $parameters);
	}
}
