<?php
namespace Drupal\dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Dashboard page.
 */
class Dashboard extends ControllerBase
{

  /**
   * Returns a page.
   *
   * @return array
   *   The rendereable array.
   */
  public function page()
  {
    return [
      '#markup' => '<h1>Automation Dashboard</h1>
                    <div id="matrix"></div>
                    <div id="legend">
                      <h3>Legend</h3>
                      <ul>
                        <li class="yellow">
                          <img src="/modules/contrib/dashboard/images/yellow.png">
                          <span>Jobs that do not have any updates in the last hour</span>
                        </li>
                        <li class="wip">
                          <img src="/modules/contrib/dashboard/images/wip.png">
                          <span>Jobs currently running without failures so far</span>
                        </li>
                        <li class="green">
                          <img src="/modules/contrib/dashboard/images/green.png">
                          <span>All scenarios passed in the last hour</span>
                        </li>
                        <li class="red">
                          <img src="/modules/contrib/dashboard/images/red.png">
                          <span>Scenarios that failed in the last hour</span>
                        </li>
                      </ul>
                    </div>
                    <div id="failures">
                        <h2>Failures</h2>
                        <div class="list"></div>
                    </div>
                    ',
      '#attached' => [
        'library' => [
          'dashboard/main',
        ],
      ],
    ];
  }

}
