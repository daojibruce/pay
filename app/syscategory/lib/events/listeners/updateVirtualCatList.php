<?php
class syscategory_events_listeners_updateVirtualCatList
{

	public function updateVirtualcatList(){
		return kernel::single('syscategory_data_virtualcat')->makeTree();
	}
}

