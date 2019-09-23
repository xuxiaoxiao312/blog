<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Dflydev\ApacheMimeTypes\PhpRepository;

class UploadsManager
{
  protected $disk;
  protected $mimeDetect;

  public function __construct(PhpRepository $mimeDetect)
  {
    // 指定磁盘
    $this->disk = Storage::disk(config('blog.uploads.storage'));
    $this->mimeDetect = $mimeDetect;

  }

  public function folderInfo($folder)
  {
    // 处理文件名
    $folder = $this->cleanFolder($folder);
    
    // 返回文件目录路径
    $breadcrumbs = $this->breadcrumbs($folder);

    $slice = array_slice($breadcrumbs, -1);
    // echo 'slice'. $slice;
    $folderName = current($slice);
    $breadcrumbs = array_slice($breadcrumbs, 0, -1);
    // echo 'breadcrumbs'. $breadcrumbs;

    $subfolders = [];

    //遍历磁盘内的所有目录
    foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
      // basename 获取路径中的文件名
      $subfolders["/$subfolder"] = basename($subfolder);
    }

    $files = [];
    // 遍历磁盘内的所有文件
    foreach ($this->disk->files($folder) as $path) {
        $files[] = $this->fileDetails($path);
    }

    // compact 创建一个包含变量名和它们的值的数组：
    return compact(
      'folder',
      'folderName',
      'breadcrumbs',
      'subfolders',
      'files'
  );
  }

  // 处理文件路径
  protected function cleanFolder($folder)
  {
    return '/' . trim(str_replace('..', '', $folder), '/'); 
  }
  /**
   * 返回当前目录路径
   */
  protected function breadcrumbs($folder)
  {
    $folder = trim($folder, '/');
    $crumbs = ['/' => 'root'];

    if (empty($folder)) {
      // echo 'crumbs'. $crumbs;
      return $crumbs;
    }

    // explode = split
    $folders = explode('/', $folder);
    $build = '';

    foreach ($folders as $folder) {
      // . 连接字符串 a .= b 即 a = a . b
      $build .= '/' . $folder;
      // echo 'build'. $build;
      $crumbs[$build] = $folder;
    }

    return $crumbs;
  }

  /**
   * 返回文件详细信息数组
   */
  public function fileDetails($path)
  {
    $path = '/' . ltrim($path, '/');
    return [
      'name' => basename($path),
      'fullPath' => $path,
      'webPath' => $this->fileWebpath($path),
      'mimeType' => $this->fileMimeType($path),
      'size' => $this->fileSize($path),
      'modified' => $this->fileModified($path),
    ];
  }

  /**
   * 返回文件完整的web路径
   */
  public function fileWebpath($path) 
  {
    $path = rtrim(config('blog.uploads.webpath'), '/'). '/' . ltrim($path, '/');
    return url($path);
  }
  /**
   * 返回文件MIME类型
   */
  public function fileMimeType($path)
  {
    return $this->mimeDetect->findType(
      pathinfo($path, PATHINFO_EXTENSION)
    );
  }
  /**
   * 返回文件大小
   */
  public function fileSize($path)
  {
      return $this->disk->size($path);
  }

  /**
   * 返回最后修改时间
   */
  public function fileModified($path)
  {
      return Carbon::createFromTimestamp(
          $this->disk->lastModified($path)
      );
  }
  /**
   * 创建目录
   */
  public function createDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);

    if($this->disk->exists($folder)) {
      return "目录" .$folder. "已存在";
    }

    return $this->disk->makeDirectory($folder);
  }
  /**
   * 删除目录
   */
  public function deleteDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);
    // 目录下的所有目录和所有文件的数组
    $filesFolder = array_merge(
      $this->disk->directories($folder),
      $this->disk->files($folder)
    );
    if(! empty($filesFolder)) {
      return  '该目录必须为空方可删除';
    }

    return $this->disk->deleteDirectory($folder);
  }
  /**
   * 删除文件
   */
  public function deleteFile($path)
  {
    $path = $this->cleanFolder($path);
    if(! $this->disk->exists($path)) {
      return '文件不存在';
    }

    return $this->disk->delete($path);
  }
  /**
   * 保存文件
   */
  public function saveFile($path,$content)
  {
    $path = $this->cleanFolder($path);

    if($this->disk->exists($path)) {
      return '文件已存在';
    }
    return $this->disk->put($path, $content);
  }

}