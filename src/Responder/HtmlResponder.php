<?php
namespace Spark\Responder;

class HtmlResponder extends AbstractResponder
{

    public static function accepts()
    {
        return ['text/html'];
    }

    protected function responseBody($data)
    {
        if (isset($data)) {
            $this->response = $this->response->withHeader('Content-Type', 'text/html');
            if (is_array($data)) {
                $this->response->getBody()->write(htmlspecialchars(json_encode($data)));
            } else {
                $this->response->getBody()->write($data);
            }
        }
    }

}
