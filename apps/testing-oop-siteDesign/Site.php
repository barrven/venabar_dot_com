<?php
class Site {
    private $headers;
    private $footers;
    private $page;

    public function __construct(Page $page=null) {
        $this->headers = array();
        $this->footers = array();
        $this->page = $page;
    }

    public function __destruct() {
        // clean up here
    }

    public function render() {
        foreach($this->headers as $header) {
            $title = $this->page->title;
            include $header;
        }

        //don't render the page if it's not set yet
        if ($this->page){
            $this->page->render();
        }

        foreach($this->footers as $footer) {
            include $footer;
        }
    }

    public function addHeader($file) {
        $this->headers[] = $file;
    }

    public function addFooter($file) {
        $this->footers[] = $file;
    }

    public function setPage(Page $page) {
        $this->page = $page;
    }
}