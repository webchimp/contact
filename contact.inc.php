<?php

	class ChimpRegistry{

		public $registry_form;
		public $registry_name;
		public $registry_email;
		public $registry_comments;
		public $registry_metas;
		public $dbh;

		/**
		 * Construct the registry object
		 */
		public function __construct($database_file = 'contact.sqlite'){

			$this->registry_form = 'no-specified';
			$this->registry_name = 'No Name Specified';
			$this->registry_email = 'noemail@specified.mx';
			$this->registry_comments = '';
			$this->registry_metas = array();

			//connect to SQLite database
			try{
				$this->dbh = new PDO("sqlite:{$database_file}");
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e){ echo $e->getMessage(); }

		} // END public function __construct

		/**
		 * Add Meta
		 */
		function addMeta($name, $value, $alias = '') {

			$this->registry_metas[] = array('name' => $name, 'value' => $value, 'alias' => $alias);
		}

		/**
		 * Insert Registry in database
		 */
		function insert() {

			try{
				$registry = $this->dbh->prepare("INSERT INTO contact(name, email, comments, form, date) VALUES (:name, :email, :comments, :form, datetime('now'))");
				$registry->bindParam(':name', $this->registry_name);
				$registry->bindParam(':email', $this->registry_email);
				$registry->bindParam(':comments', $this->registry_comments);
				$registry->bindParam(':form', $this->registry_form);

				$registry->execute();
				$registry_id = $this->dbh->lastInsertId();

				foreach($this->registry_metas as $key => $meta){

					$meta_registry = $this->dbh->prepare("INSERT INTO contact_meta(id, meta, value, alias) VALUES (:id, :meta, :value, :alias)");
					$meta_registry->bindParam(':id', $registry_id);
					$meta_registry->bindParam(':meta', $meta['name']);
					$meta_registry->bindParam(':value', $meta['value']);
					$meta_registry->bindParam(':alias', $meta['alias']);

					$meta_registry->execute();
				}

				return true;
			}

			catch(PDOException $e){ return $e->getMessage(); }
		}
	} // END class ChimpRegistry

?>