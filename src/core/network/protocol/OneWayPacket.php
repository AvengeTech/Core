<?php

namespace core\network\protocol;

abstract class OneWayPacket extends ConnectPacket {

	//Packets extending this class will not
	//Send any response data back, or wait for it

}
