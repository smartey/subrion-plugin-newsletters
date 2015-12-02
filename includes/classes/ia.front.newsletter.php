<?php
//##copyright##

class iaNewsletter extends abstractCore
{
	protected static $_table = 'newsletter_subscribers';


	public function emailExists($email)
	{
		return $this->iaDb->exists('`email` = :email', array('email' => $email), self::getTable());
	}

	public function tokenExists($token)
	{
		return $this->iaDb->exists('`token` = :token', array('token' => $token), self::getTable());
	}

	public function insert($data)
	{
		$this->iaDb->setTable(self::getTable());
		$this->iaDb->insert($data);
		$this->iaDb->resetTable();
	}

	public function update($data, $where, $addit)
	{
		$this->iaDb->update($data, $where, $addit, self::getTable());
	}

	public function delete($token)
	{
		$this->iaDb->delete('`token` = :token', self::getTable(), array('token' => $token));
	}

	public function generateLetterContent($topics, $last_sent)
	{
		foreach($this->iaCore->packagesData as $package)
		{
			$available_packages[] = $package['name'];
		}

		$html = '';

		$topics = explode('|', $topics);

		foreach($topics as $topic)
		{
			$topic = explode(':', $topic);
			$extra = $topic[0];
			$cats = $topic[1];

			if(in_array($extra, $available_packages))
			{
				include 'plugins/newsletters/packages/' . $extra . '/items.php';

				if(!empty($items))
				{
					$html .= '<strong><em><p style="font-size:22px;text-transform:capitalize;">' . $this->iaCore->packagesData[$extra]['title'] . '</p></em></strong><br />';
					foreach($items as $item)
					{
						$html .= '<p style="font-size:16px;"><strong><a href="' . $item['url'] . '">' . $item['title'] . '</a></strong> - <em>' . date('F j, Y', $item['date']) . '</em></p>';
						$html .= '<span style="font-size:14px;">' . $item['description'] . '</span><br /><br />';
					}
				}
			}
		}

		return $html;
	}

	public function emailConfirmation($email, $token)
	{
		$stmt = '`email` = :email AND `token` = :token';
		$this->iaDb->bind($stmt, array('email' => $email, 'token' => $token));

		return (bool)$this->iaDb->update(array('status' => 'active'), $stmt, null, self::getTable());
	}
}