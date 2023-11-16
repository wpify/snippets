<?php

namespace Wpify\Snippets;

class DisableDefaultAsRunners {
	public function __construct() {
		add_action( 'init', array( $this, 'disable_default_runner' ), 10 );
	}

	/**
	 * Disable default Action Scheduler Runners
	 * https://github.com/woocommerce/action-scheduler-disable-default-runner
	 * @return void
	 */
	function disable_default_runner(): void {
		if ( class_exists( 'ActionScheduler' ) ) {
			remove_action( 'action_scheduler_run_queue', array( \ActionScheduler::runner(), 'run' ) );
		}
	}
}
