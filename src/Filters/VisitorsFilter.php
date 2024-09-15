<?php

namespace Aselsan\Visitors\Filters;

use Aselsan\Visitors\Config\Visitors;
use Aselsan\Visitors\Models\VisitorModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

/**
 * Visitors Filter
 *
 * Records visitors for matching routes.
 */
class VisitorsFilter implements FilterInterface
{
    protected Visitors $config;
    protected VisitorModel $model;

    public function __construct()
    {
        $this->config = config(Visitors::class);
        $this->model  = model(VisitorModel::class);
    }

    public function before(RequestInterface $request, $arguments = null): void
    {
        $this->record($request);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Ignoring redirects
        if ($this->config->ignoreRedirects && $response instanceof RedirectResponse) {
            return;
        }
        // Ignore empty responses
        if ($this->config->requireBody && empty($response->getBody())) {
            return;
        }
        // Ignore non-HTML response
        if ($this->config->requireHtml && strpos($response->getHeaderLine('Content-Type'), 'html') === false) {
            return;
        }

        $this->record($request);
    }

    /**
     * Records a visit, either adding a new row or
     * increasing the view count on an existing one.
     *
     * @throws RuntimeException
     */
    final protected function record(RequestInterface $request): void
    {
        if (is_cli() && ENVIRONMENT !== 'testing') {
            return; // @codeCoverageIgnore
        }

        if (! $request instanceof IncomingRequest) {
            throw new RuntimeException(static::class . ' requires an IncomingRequest object.');
        }

        // Ignore AJAX requests
        if ($this->config->ignoreAjax && $request->isAJAX()) {
            return;
        }

        // Verify helper function from codeigniter4/authentication-implementation
        if (! function_exists('user_id') && $this->config->trackingMethod === 'user_id') {
            throw new RuntimeException('The user_id() function must be available to track by user ID.'); // @codeCoverageIgnore
        }

        // Use the Request to create a Visit
        $visit = $this->model->makeFromRequest($request);

        // Apply any transformations
        foreach ($this->config->transformers as $transformer) {
            $visit = $transformer::transform($visit, $request);

            // Check for a short-circuit
            if ($visit === null) {
                return;
            }
        }

        // Check for an existing similar record
        if ($similar = $this->model->findSimilar($visit)) {
            // Increment view count and update
            $similar->views++;
            $this->model->save($similar);

            return;
        }

        // Create a new visit record
        if ($this->model->save($visit)) {
            return;
        }

        $error = implode(' ', $this->model->errors());

        throw new RuntimeException('Failed to create visit record: ' . $error);
    }
}
