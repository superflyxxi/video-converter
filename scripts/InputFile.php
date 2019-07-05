<?

include_once "Stream.php";

public class InputFile {

	__construct($json) {
		foreach ($json["streams"] as $stream) {
			$this->streams[$stream["index"]] = new Stream($stream);
		}
	}

	public $streams = array();

}

?>

