<?php
App::uses('AbstractTransport', 'Network/Email');
App::uses('Cake2PsrLog', 'SparkPost.Log');

/**
 * @see https://developers.sparkpost.com/api/
 * @see https://github.com/SparkPost/sparkpost
 */
class SparkPostTransport extends AbstractTransport {

	/**
	 * CakeEmail
	 *
	 * @var CakeEmail
	 */
	protected $_cakeEmail;

	/**
	 * CakeEmail headers
	 *
	 * @var array
	 */
	protected $_headers;

	/**
	 * Configuration to transport
	 *
	 * @var mixed
	 */
	protected $_config = array();

	/**
	 * Sends out email via SparkPost
	 *
	 * @param CakeEmail $email
	 * @return array
	 */
	public function send(CakeEmail $email) {

		// CakeEmail
		$this->_cakeEmail = $email;

		$this->_config = $this->_cakeEmail->config();

		$this->_headers = $this->_cakeEmail->getHeaders();

		// Not allowed by SparkPost
		unset($this->_headers['Content-Type']);
		unset($this->_headers['Content-Transfer-Encoding']);
		unset($this->_headers['MIME-Version']);
		unset($this->_headers['X-Mailer']);

		$from = $this->_cakeEmail->from();
		list($fromEmail) = array_keys($from);
		$fromName = $from[$fromEmail];

		$message = [
			'options' => [],
			'content' => [
				'html' => $this->_cakeEmail->message('html'),
				'text' => $this->_cakeEmail->message('text'),
				'from' => [
					'name' => $fromName,
					'email' => $fromEmail
				],
				// SparkPost does not like RFC 2047 encoding for the subject, see https://developers.sparkpost.com/api/transmissions.
				'subject' => mb_decode_mimeheader($this->_cakeEmail->subject()),
			],
			'recipients' => [],
		];

		if ( ! empty($this->_cakeEmail->replyTo())) {
			$message['content']['reply_to'] = array_values($this->_cakeEmail->replyTo())[0];
		}

		if ( ! empty($this->_config['sparkpost']['options'])) {
			$message['options'] = $this->_config['sparkpost']['options'];
		}
		$message['options'] = (object) $message['options'];

		foreach ($this->_cakeEmail->to() as $email => $name) {
			$message['recipients'][] = [
				'address' => [
					'email' => $email,
					'name' => $name,
				],
				'tags' => empty($this->_headers['tags']) ? null : $this->_headers['tags'],
			];
		}

		foreach ($this->_cakeEmail->cc() as $email => $name) {
			$message['recipients'][] = [
				'address' => [
					'email' => $email,
					'name' => $name,
				],
				'tags' => empty($this->_headers['tags']) ? null : $this->_headers['tags'],
			];
		}

		foreach ($this->_cakeEmail->bcc() as $email => $name) {
			$message['recipients'][] = [
				'address' => [
					'email' => $email,
					'name' => $name,
				],
				'tags' => empty($this->_headers['tags']) ? null : $this->_headers['tags'],
			];
		}

		unset($this->_headers['tags']);

		$attachments = $this->_cakeEmail->attachments();
		if (!empty($attachments)) {
			foreach ($attachments as $file => $data) {
				if (!empty($data['contentId'])) {
					$message['content']['inline_images'][] = array(
						'type' => $data['mimetype'],
						'name' => $data['contentId'],
						'data' => base64_encode(file_get_contents($data['file'])),
					);
				} else {
					$message['content']['attachments'][] = array(
						'type' => $data['mimetype'],
						'name' => $file,
						'data' => base64_encode(file_get_contents($data['file'])),
					);
				}
			}
		}

		$message = array_merge($message, $this->_headers);

		// Load SparkPost configuration settings
		$config = ['key' => $this->_config['sparkpost']['api_key']];
		if (isset($this->_config['sparkpost']['timeout'])) {
			$config['timeout'] = $this->_config['sparkpost']['timeout'];
		}

		$httpClient = new Http\Adapter\Guzzle6\Client($this->__getClient());
		$sparkpost = new SparkPost\SparkPost($httpClient, $config);

		// Send message
		try {
			$promise = $sparkpost->transmissions->post($message);
			return $promise->wait();
		} catch(SparkPost\APIResponseException $e) {
				// TODO: Determine if BRE is the best exception type
				throw new BadRequestException(sprintf('SparkPost API error %d (%d): %s (%s)',
					$e->getAPICode(), $e->getCode(), ucfirst($e->getAPIMessage()), $e->getAPIDescription()));
		}
	}

	private function __getClient() {
		$config = [];
		if (isset($this->_config['sparkpost']['log'])) {
			$stack = GuzzleHttp\HandlerStack::create();
			$stack->push(
				GuzzleHttp\Middleware::log(
				class_exists('\\Cake\\Log\\Log') ? new \Cake\Log\Log() : new Cake2PsrLog(),
					new GuzzleHttp\MessageFormatter(isset($this->_config['sparkpost']['log']['format']) ? $this->_config['sparkpost']['log']['format'] : '{response}'),
					isset($this->_config['sparkpost']['log']['level']) ? $this->_config['sparkpost']['log']['level'] : 'debug'
				)
			);
			$config = [
				'handler' => $stack,
			];
		}
		return new GuzzleHttp\Client($config);
	}

}
