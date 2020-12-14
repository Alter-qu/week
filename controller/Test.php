<?php

namespace app\weektest1\controller;

use app\weektest1\model\Circle;
use think\Controller;
use think\Request;

class Test extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function show()
    {
        $list=Circle::order("createtime","DESC")->paginate(3);
        return view('',['list'=>$list]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param=$request->param();
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下、之前先验证
        $info = $file->validate(['size'<=1024*1024*3,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
        $param['img']=$info->getSaveName();
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
        //表单验证
        $result = $this->validate(
           $param,
            [
                'name'  => 'require|max:50',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        //入库
        $param['createtime']=date("Y-m-d h:i:s",time());
        $res=Circle::create($param,true);
        if ($res){
            //存入缓存
            cache("circle",$res);
            //dump($res->toArray());
            return redirect("Test/show");
        }else{
            return json(['code'=>500,'msg'=>'fail','result'=>null]);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function search(Request $request)
    {
        $param=$request->param();
        $name=$param['name'];
        $res=Circle::where('name',"like","%$name%")->select();
        return view('',['list'=>$res,'name'=>$name]);
    }


    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $res=Circle::where('static',1)->where("id",$id)->delete();
        if ($res){
            return redirect('Test/show');
        }else{
            $this->error("已经显示，不可删除");
        }
    }
}
