<?php
/**
 * @author Christian Archer
 * @copyright 2014, Christian Archer
 * @license AGPL-3.0
 */

namespace WPReadme2Markdown\Web;

use Slim\Http\Request;
use Slim\Http\Response;
use WPReadme2Markdown\Converter;

class Controller
{
    private $request;
    private $response;
    private $arguments;

    public function __construct(Request $req,  Response $res, $args = [])
    {
        $this->request      = $req;
        $this->response     = $res;
        $this->arguments    = $args;
    }

    public function index()
    {
        $this->render('index');
    }

    public function about()
    {
        $this->render('about', [
            'title' => 'Description',
        ]);
    }

    public function wp2md()
    {
        $wp2md_readme = file_get_contents(App::$path . '/vendor/wpreadme2markdown/wpreadme2markdown/README.md');

        $this->render('wp2md', [
            'readme' => \Parsedown::instance()->text($wp2md_readme),
            'title'  => 'WP2MD CLI'
        ]);
    }

    public function convert()
    {
        $readme = $this->request->getParam('readme-text');

        if (isset($_FILES['readme-file']) && $_FILES['readme-file']['error'] === UPLOAD_ERR_OK) {
            $readme = file_get_contents($_FILES['readme-file']['tmp_name']);
        }

//        if (empty($readme)) {
//            App::$slim->flashNow('error', 'Either Readme content or Readme file must be set');
//            $this->index();
//            return;
//        }

        $slug = $this->request->getParam('plugin-slug');

        if (empty(trim($slug))) {
            $slug = null;
        }

        $markdown = Converter::convert($readme, $slug);

        // also render demo
        $markdown_html = \Parsedown::instance()->text($markdown);

        $this->render('convert', [
            'markdown' => $markdown,
            'markdown_html' => $markdown_html,
        ]);
    }

    public function download()
    {
        $markdown = $this->request->getParsedBodyParam('markdown');

        $response = $this->response->
            withHeader('Content-Type', 'application/octet-stream')->
            withHeader('Content-Transfer-Encoding', 'binary')->
            withHeader('Content-disposition', 'attachment; filename="README.md"')->
            withBody($markdown);

        return $response;
    }

    private function render($template, $args = [])
    {
        App::$slim->view->render($this->response, $template, $args);
    }
}
