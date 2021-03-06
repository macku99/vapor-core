<?php

namespace Laravel\Vapor\Tests;

use Mockery;
use Aws\Sqs\SqsClient;
use PHPUnit\Framework\TestCase;
use Laravel\Vapor\Queue\VaporQueue;

class VaporQueueTest extends TestCase
{
    public function tearDown() : void
    {
        Mockery::close();
    }


    public function test_proper_payload_array_is_created()
    {
        $sqs = Mockery::mock(SqsClient::class);

        $job = new FakeJob;

        $sqs->shouldReceive('sendMessage')->once()->with([
            'QueueUrl' => '/test-vapor-queue-url',
            'MessageBody' => json_encode([
                'displayName' => FakeJob::class,
                'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                'maxTries' => null,
                'timeout' => null,
                'timeoutAt' => null,
                'data' => [
                    'commandName' => FakeJob::class,
                    'command' => serialize($job),
                ],
                'attempts' => 0,
            ]),
        ])->andReturnSelf();

        $sqs->shouldReceive('get')->andReturn('attribute-value');

        $queue = new VaporQueue($sqs, 'test-vapor-queue-url');

        $this->assertEquals('attribute-value', $queue->push($job));
    }
}
