<?php

namespace KieranFYI\Logging\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Logging\Models\Logs\ModelLog;

/**
 * @mixin Model
 */
trait LoggingTrait
{
    /**
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(ModelLog::class, 'model');
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $log = new ModelLog([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'data' => $this->toArray(),
        ]);

        /** @var Model $this */
        $log->model()->associate($this);

        if (Auth::hasUser() && in_array(Model::class, class_uses_recursive(Auth::user()))) {
            /** @var Model $user */
            $user = Auth::user();
            $log->user()->associate($user);
        }

        $log->save();
    }

    /**
     * @param string $level
     * @param string|Exception $message
     * @param array $context
     */
    public function exception(string $level, string|Exception $message, array $context = []): void
    {
        if ($message instanceof Exception) {
            $this->log($level, $message->getMessage(), ['context' => $context, 'trace' => $message->getTrace()]);
        } else {
            $this->log($level, $message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array $context
     */
    public function error(string|Exception $message, array $context = []): void
    {
        $this->exception('error', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array $context
     */
    public function critical(string|Exception $message, array $context = []): void
    {
        $this->exception('critical', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array $context
     */
    public function emergency(string|Exception $message, array $context = []): void
    {
        $this->exception('emergency', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function security(string $message, array $context = []): void
    {
        $this->log('security', $message, $context);
    }
}
