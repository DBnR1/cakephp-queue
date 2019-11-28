<?php
/**
 * @author Mark Scherer
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Queue\Mailer\Transport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use Cake\ORM\TableRegistry;

/**
 * Send mail using Queue plugin
 */
class SimpleQueueTransport extends AbstractTransport {

	/**
	 * Send mail
	 *
	 * @param \Cake\Mailer\Message $message
	 * @return array
	 */
	public function send(Message $message): array {
		if (!empty($this->_config['queue'])) {
			$this->_config = $this->_config['queue'] + $this->_config;
			$message->setConfig((array)$this->_config['queue'] + ['queue' => []]);
			unset($this->_config['queue']);
		}

		$settings = [
			'from' => [$message->getFrom()],
			'to' => [$message->getTo()],
			'cc' => [$message->getCc()],
			'bcc' => [$message->getBcc()],
			'charset' => [$message->getCharset()],
			'replyTo' => [$message->getReplyTo()],
			'readReceipt' => [$message->getReadReceipt()],
			'returnPath' => [$message->getReturnPath()],
			'messageId' => [$message->getMessageId()],
			'domain' => [$message->getDomain()],
			'headers' => [$message->getHeaders()],
			'headerCharset' => [$message->getHeaderCharset()],
			//'theme' => [$message->getTheme()],
			//'profile' => [$message->getProfile()],
			'emailFormat' => [$message->getEmailFormat()],
			'subject' => method_exists($message, 'getOriginalSubject') ? [$message->getOriginalSubject()] : [$message->getSubject()],
			'transport' => [$this->_config['transport']],
			'attachments' => [$message->getAttachments()],
			//'template' => [$message->getTemplate()],
			//'layout' => [$message->getLayout()],
			//'viewVars' => [$message->getViewVars()],
		];

		foreach ($settings as $setting => $value) {
			if (array_key_exists(0, $value) && ($value[0] === null || $value[0] === [])) {
				unset($settings[$setting]);
			}
		}

		$QueuedJobs = $this->getQueuedJobsModel();
		$result = $QueuedJobs->createJob('Email', ['settings' => $settings]);
		$result['headers'] = '';
		$result['message'] = '';

		return $result->toArray();
	}

	/**
	 * @return \Queue\Model\Table\QueuedJobsTable
	 */
	protected function getQueuedJobsModel() {
		/** @var \Queue\Model\Table\QueuedJobsTable $table */
		$table = TableRegistry::get('Queue.QueuedJobs');

		return $table;
	}

}
