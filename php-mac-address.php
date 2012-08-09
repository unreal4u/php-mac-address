<?php

class MAC_Address {
	
	/**
	 * Regular expression for matching and validating a MAC address
	 * @var string
	 */
	private $valid_mac = "([0-9A-F]{2}[:-]){5}([0-9A-F]{2})";
	
	/**
	 * An array of valid MAC address characters
	 * @var array
	 */
	private $mac_address_vals = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");

	/**
	 * @return string generated MAC address
	 */
	public function generate_mac_address() {
		$vals = $this->mac_address_vals;
		if (count($vals) >= 1) {
			$mac = array("00"); // set first two digits manually
			while (count($mac) < 6) {
				shuffle($vals);
				$mac[] = $vals[0] . $vals[1];
			}
			$mac = implode(":", $mac);
		}
		return $mac;
	}

	/**
	 * Make sure the provided MAC address is in the correct format
	 * @param string $mac
	 * @return bool TRUE if valid; otherwise FALSE
	 */
	public function validate_mac_address($mac) {
		return (bool) preg_match("/^{$this->valid_mac}$/i", $mac);
	}

	/**
	 * @param string $command
	 * @return string output from command that was ran
	 */
	protected function run_command($command) {
		return shell_exec($command);
	}

	/**
	 * @return string Systems current MAC address
	 */
	public function get_current_mac_address($interface) {
		if (!empty($interface)) {
			$ifconfig = $this->run_command("ifconfig {$interface}");
			preg_match("/{$this->valid_mac}/i", $ifconfig, $ifconfig);
			return trim(strtoupper($ifconfig[0]));
		}
	}

	/**
	 * @param string $mac
	 * @return bool Returns true on success else returns false
	 */
	public function set_fake_mac_address($mac = "", $interface) {
		if (empty($mac)) {
			$new_mac = $this->generate_mac_address();
		} else {
			$new_mac = $mac;
		}
		$this->run_command("ifconfig {$interface} down");
		$this->run_command("ifconfig {$interface} hw ether {$new_mac}");
		$this->run_command("ifconfig {$interface} up");
		if ($this->get_current_mac_address() == $new_mac) {
			return true;
		} else {
			return false;
		}
	}

}
