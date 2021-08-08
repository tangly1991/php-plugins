<?php
class PathTree {
	
	/**
	 * mmt 2021-8-4
	 * 生成树型结构所需要的2维数组,添加'spacer' => ' │ ├─ ',可能添加'selected'=>'selected'。
	 *
	 		//存入数据库path字段例子：
	 		if ($data['parent'] != 0) {
				$parent = $this->db->where( "term_id={$data['parent']}" )->find('terms');
				$path = $parent['path'] . '-' . $data['parent'];
			} else {
				$path = '0';
			}
			$data['path'] = $path; //存入数据库path字段形如“0”，“0-6-7”，不包含自己的id
			//用法：
        	import('PathTree');
        	$parentid = intval($_REQUEST['parent']);
        	$tree = new PathTree();
        	$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        	$tree->nbsp = '---';
        	// $result = $this->db->order("listorder asc")->select('terms');
        	$result=json_decode('[{"term_id":"6","name":"\\u540d\\u79f0","slug":"","taxonomy":"article","description":"\\u63cf\\u8ff0","parent":"0","count":"0","path":"0","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"7","name":"fd","slug":"","taxonomy":"article","description":"zcvvzx","parent":"6","count":"0","path":"0-6","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"8","name":"vczvz","slug":"","taxonomy":"article","description":"vxcvvzcvzc","parent":"7","count":"0","path":"0-6-7","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"9","name":"32432","slug":"","taxonomy":"picture","description":"fd3223","parent":"0","count":"0","path":"0","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"10","name":"rwer","slug":"","taxonomy":"picture","description":"fda","parent":"8","count":"0","path":"0-6-7-8","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"11","name":"fds","slug":"","taxonomy":"article","description":"zvczvdsf","parent":"9","count":"0","path":"0-9","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"12","name":"vz","slug":"","taxonomy":"article","description":"vczzvc","parent":"9","count":"0","path":"0-9","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"},{"term_id":"13","name":"\\u8303\\u5fb7\\u8428","slug":"","taxonomy":"article","description":"\\u6ce8\\u518c","parent":"7","count":"0","path":"0-6-7","seo_title":"","seo_keywords":"","seo_description":"","list_tpl":"list","one_tpl":"article","listorder":"0","status":"1"}]',true);
        	$tree->init($result);
        	$tree=$tree->get_tree('parent','term_id',$parentid);
        	exit(!print_r(str_repeat('-', 50) . '<br/>' . basename(__FILE__) . ' line ' . __LINE__ . '<pre>') . (var_export(json_encode($tree))) . !print_r('</pre>'));

	 */
	public $arr = array();
	
	/**
	 * 生成树型结构所需修饰符号，可以换成图片
	 * @var array
	*/
	public $icon = array('│', '├─', '└─');
	public $nbsp = "&nbsp;";
	
	public function init($arr=array()) {
		$this->arr = $arr;
		return is_array($arr);
	}
	
	// public function get_tree(){
	// 	$array=$this->arr;
	// 	foreach ($array as $key=> $r){
	// 		$level=count(explode("-", $r["path"]))-1;
	// 		$array[$key]["level"]=$level;
	// 		if($level==0){
	// 			$array[$key]["spacer"]='';
	// 		}
	// 		else{
	// 			$array[$key]["spacer"]=$this->get_spacer($level-1);
	// 		}
	// 	}
	// 	return $array;
	// }
	
	/**
	 * 处理数据库中得到的tree菜单二维数组，添加'spacer' => '   │    ├─ ',可能添加'selected'=>'selected'。
	 * @Author   mmt
	 * @DateTime 2021-08-04
	 * @param    integer    $selected_id 选中的ID
	 * @return   [type]               返回处理过的数组
	 */
	public function get_tree($pid='pid',$id='id',$selected_id=-1){
		$array=$this->arr;
		$array=mmt_make_tree($array,$pid,$id);
        $array=imp($array);
		$array=$this->_getspacer($array,$pid,$id,$selected_id);
		return $array;
	}

	function _getspacer($array,$pid='pid',$id='id',$selected_id=-1){
		$path=array();
		foreach ($array as $k => $v) {
			$path[$v['path']] += 1;
		}
		foreach ($array as $key=> $r){
			if($selected_id!=-1 && $array[$key][$id]==$selected_id)
				$array[$key]['selected']='selected';
			$level=count(explode("-", $r["path"]))-1;
			$array[$key]["level"]=$level;
			if($level==0){
				$array[$key]["spacer"]='';
			}
			else{
				$path[$r['path']] -= 1;
				if($path[$r['path']]>0)
					$array[$key]["spacer"]=$this->get_spacer($level-1,1);
				else
					$array[$key]["spacer"]=$this->get_spacer($level-1);
			}
		}
		return $array;
	}

	public function get_spacer($count,$icon=2){
		$spacer="";
		for ($i=0;$i<$count;$i++){
			// $spacer.=$this->nbsp;
			$spacer.=$this->icon[0];
		}
		//
		if($icon==1)
			$spacer.=$this->icon[1];
		else
			$spacer.=$this->icon[2];
		return $spacer;
		
	}
}

/*
// 二维数组 生成无限分类树(多维，循环)
// @Author   mmt
function mmt_make_tree($items, $pid_key = 'pid', $id_key = 'id', $children='children')
{
    $map  = array();
    $tree = array();
    foreach ($items as &$it){ $map[$it[$id_key]] = &$it; }  //数据的ID名生成新的引用索引树
    // 遍历所有项目
    foreach ($items as &$it) {
        // 如果该项目存在父级，则添加到父级的子列表
        $parent = &$map[$it[$pid_key]];
        if ($parent) {
            $parent[$children][] = &$it;
        }
        // 否则添加到顶级
        else {
            $tree[] = &$it;
        }
    }
    return $tree;
}

// 多维数组转二维数组
function imp($tree, $children='children') {
  $imparr = array();
  foreach($tree as $w) {
    if(isset($w[$children])) {
      $t = $w[$children];
      unset($w[$children]);
      $imparr[] = $w;
      if(is_array($t)) $imparr = array_merge($imparr, imp($t, $children));
    } else {
      $imparr[] = $w;
    }
  }
  return $imparr;
}
*/