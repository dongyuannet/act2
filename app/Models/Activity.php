<?php   
namespace App\Models;  
use Illuminate\Database\Eloquent\Model;  
use Illuminate\Support\Facades\DB;  
class Activity extends Model{  
    //设置表名  
  
    protected $table = "activity";  
    // public $timestamps = false;  
   	public function setPicsAttribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['pics'] = json_encode($image);
	    }
	}

	public function getPicsAttribute($image)
	{
		//$image = explode(',',$image)?explode(',',$image):'';
	    return json_decode($image);
	}

	public function setTitletypeAttribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['titletype'] = json_encode($image);
	    }
	}

	public function getTitletypeAttribute($image)
	{
	    return json_decode($image);
	}


	public function setReward1Attribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['reward1'] = json_encode($image);
	    }
	}

	public function getReward1Attribute($image)
	{
	    return json_decode($image);
	}

	public function setReward2Attribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['reward2'] = json_encode($image);
	    }
	}

	public function getReward2Attribute($image)
	{
	    return json_decode($image);
	}


	public function setScoreruleAttribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['scorerule'] = json_encode($image);
	    }
	}

	public function getScoreruleAttribute($image)
	{
	    return json_decode($image);
	}

	public function setDanmuAttribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['danmu'] = json_encode($image);
	    }
	}

	public function getDanmuAttribute($image)
	{
	    return json_decode($image);
	}

	public function setBrandAttribute($image)
	{
	    if (is_array($image)) {
	        $this->attributes['brand'] = json_encode($image);
	    }
	}

	public function getBrandAttribute($image)
	{
	    return json_decode($image);
	}
}  