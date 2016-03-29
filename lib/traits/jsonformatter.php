<?
namespace Notagency\Base\Traits;

trait JsonFormatter
{
    protected function applyJsonHeaders()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
    }

    public function showResult()
    {
        $this->applyJsonHeaders();
        echo json_encode($this->arResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    protected function catchException(\Exception $exception)
    {
        $this->arResult = [
            'error' => true,
            'message' => $exception->getMessage(),
        ];
        $this->showResult();
    }
}