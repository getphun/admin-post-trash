<?php
/**
 * Post trash controller
 * @package admin-post-trash
 * @version 0.0.1
 * @upgrade true
 */

namespace AdminPostTrash\Controller;

use AdminPost\Model\PostHistory as PHistory;
use PostCanal\Model\PostCanal as PCanal;

class PostController extends \AdminController
{
    
    private function _defaultParams(){
        return [
            'title'             => 'Post Trash',
            'nav_title'         => 'Post',
            'active_menu'       => 'post',
            'active_submenu'    => 'post-trash',
            'total'             => 0,
            'pagination'        => []
        ];
    }

    public function indexAction(){
        if(!$this->user->login)
            return $this->loginFirst('adminLogin');
        if(!$this->can_i->read_post_trash)
            return $this->show404();
        
        $params = $this->_defaultParams();
        $params['reff']  = $this->req->url;
        $params['posts'] = [];
        
        $page = $this->req->getQuery('page', 1);
        $rpp  = 20;
        $offset = $rpp * ( $page - 1 );
        
        $files = array_diff(scandir(BASEPATH . '/etc/post/trash'), ['.gitkeep', '..', '.']);
        natsort($files);
        
        $posts = array_slice($files, $offset, $rpp);
        if($posts){
            $users = [];
            foreach($posts as $index => $post){
                $ctn = file_get_contents(BASEPATH . '/etc/post/trash/' . $post);
                $post = json_decode($ctn);
                $posts[$index] = $post->{'Post\\Model\\Post'};
                $post_histories = $post->{'AdminPost\\Model\\PostHistory'};
                $post_histories = end($post_histories);
                $posts[$index]->deleted = $post_histories->created;
                $posts[$index]->deleter = $post_histories->user;
            }
            
            $params['posts'] = $posts = \Formatter::formatMany('post-trash', $posts, false, ['user', 'deleter']);
        }
        
        $params['total'] = $total = count($files);
        if($total > $rpp)
            $params['pagination'] = \calculate_pagination($page, $rpp, $total, 10);
        
        return $this->respond('post/trash/index', $params);
    }

    public function removeAction(){
        if(!$this->user->login)
            return $this->show404();
        if(!$this->can_i->remove_post_trash)
            return $this->show404();
            
        $id = $this->param->id;
        $file = BASEPATH . '/etc/post/trash/' . $id . '.json';
        if(!is_file($file))
            return $this->show404();
        
        // remove the file? :(
        unlink($file);
        
        $ref = $this->req->getQuery('ref');
        if($ref)
            return $this->redirect($ref);
        return $this->redirectUrl('adminPostTrash');
    }
    
    public function restoreAction(){
        if(!$this->user->login)
            return $this->show404();
        if(!$this->can_i->restore_post_trash)
            return $this->show404();
        
        $id = $this->param->id;
        $file = BASEPATH . '/etc/post/trash/' . $id . '.json';
        if(!is_file($file))
            return $this->show404();
        
        $cnt = file_get_contents($file);
        $trash = json_decode($cnt);
        
        foreach($trash as $model => $vals){
            if($model == 'Post\\Model\\Post')
                $vals->updated = date('Y-m-d H:i:s');
            
            if(is_array($vals)){
                foreach($vals as $index => $val)
                    $vals[$index] = (array)$val;
                $model::createMany($vals);
            }else{
                $model::create($vals);
            }
            
            switch($model){
            case 'Post\\Model\\Post':
                $this->fire('post:updated', $vals, $vals);
                $post = $vals;
                if($vals->canal){
                    $canal = PCanal::get(['id'=>$vals->canal], false);
                    if($canal)
                        $this->fire('post-canal:updated', $canal, $canal);
                }
                break;
            case 'PostCategory\\Model\\PostCategoryChain':
            case 'PostTag\\Model\\PostTagChain':
                if($model == 'PostTag\\Model\\PostTagChain'){
                    $omodel = 'PostTag\\Model\\PostTag';
                    $oprop  = 'post_tag';
                    $omodule= 'post-tag';
                }else{
                    $omodel = 'PostCategory\\Model\\PostCategory';
                    $oprop  = 'post_category';
                    $omodule= 'post-category';
                }
                $oids = array_column($vals, $oprop);
                $oobj = $omodel::get(['id'=>$oids], true);
                if($oobj){
                    foreach($oobj as $obj)
                        $this->fire($omodule.':updated', $obj, $obj);
                }
            }
        }
        
        // add history
        PHistory::create([
            'user' => $this->user->id,
            'post' => $post->id,
            'type' => 6
        ]);
        
        unlink($file);
        
        $ref = $this->req->getQuery('ref');
        if($ref)
            return $this->redirect($ref);
        return $this->redirectUrl('adminPostTrash');
    }
}