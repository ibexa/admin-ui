<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use DateTimeInterface;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\CSSLocator;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class DateAndTimePopup extends Component
{
    private const string DATETIME_FORMAT = 'd/m/Y';

    private const string SETTING_SCRIPT_FORMAT = "document.querySelector('%s %s')._flatpickr.setDate('%s', true, '%s')";

    private const string CALENDAR_CONTAINER_CLASSES_SCRIPT = "document.querySelector('%s %s')._flatpickr.calendarContainer.attributes.class.textContent";

    private const string ADD_CALLBACK_TO_DATEPICKER_SCRIPT_FORMAT = 'var fi = document.querySelector(\'%s .flatpickr-input\');
                const onChangeOld = fi._flatpickr.config.onChange;
                const onChangeNew = (dates, dateString, flatpickInstance) => {
                flatpickInstance.input.classList.add("date-set");
            };
        if (onChangeOld instanceof Array) {
                fi._flatpickr.config.onChange = [...onChangeOld, onChangeNew];
        } else if (onChangeOld) {
                fi._flatpickr.config.onChange = [onChangeOld, onChangeNew];
            } else {
                fi._flatpickr.config.onChange = onChangeNew;
            }';

    private CSSLocator $parentLocator;

    public function __construct(Session $session)
    {
        parent::__construct($session);

        $this->parentLocator = VisibleCSSLocator::empty();
    }

    public function setDate(DateTimeInterface $date, string $dateFormat = self::DATETIME_FORMAT): void
    {
        $this->getSession()->executeScript(
            sprintf(
                self::ADD_CALLBACK_TO_DATEPICKER_SCRIPT_FORMAT,
                $this->parentLocator->getSelector()
            )
        );

        $dateScript = sprintf(
            self::SETTING_SCRIPT_FORMAT,
            $this->parentLocator->getSelector(),
            $this->getLocator('flatpickrSelector')->getSelector(),
            $date->format($dateFormat),
            $dateFormat
        );
        $this->getSession()->getDriver()->executeScript($dateScript);

        $this->getHTMLPage()
            ->find($this->parentLocator)
            ->find($this->getLocator('dateSet'))
            ->assert()
            ->isVisible();
    }

    public function setTime(int $hour, int $minute): void
    {
        $calendarContainerClassesScript = sprintf(
            self::CALENDAR_CONTAINER_CLASSES_SCRIPT,
            $this->parentLocator->getSelector(),
            $this->getLocator('flatpickrSelector')->getSelector()
        );

        $isTimeOnly = str_contains(
            $this->getSession()->evaluateScript($calendarContainerClassesScript),
            'noCalendar'
        );

        if (!$isTimeOnly) {
            // get current date as it's not possible to set time without setting date
            $currentDateScript = sprintf(
                'document.querySelector("%s %s")._flatpickr.selectedDates[0].toLocaleString()',
                $this->parentLocator->getSelector(),
                $this->getLocator('flatpickrSelector')->getSelector()
            );
            $currentDate = $this->getSession()->getDriver()->evaluateScript($currentDateScript);
        }

        $valueToSet = $isTimeOnly ? sprintf('%s:%s:00', $hour, $minute) : sprintf('%s, %s:%s:00', explode(',', $currentDate)[0], $hour, $minute);
        $format = $isTimeOnly ? 'H:i:S' : 'm/d/Y, H:i:S';

        $timeScript = sprintf(
            self::SETTING_SCRIPT_FORMAT,
            $this->parentLocator->getSelector(),
            $this->getLocator('flatpickrSelector')->getSelector(),
            $valueToSet,
            $format
        );

        $this->getSession()->getDriver()->executeScript($timeScript);
    }

    public function setParentLocator(VisibleCSSLocator $locator): void
    {
        $this->parentLocator = $locator;
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->find($this->parentLocator)
            ->find($this->getLocator('flatpickrSelector'))
            ->assert()
            ->isVisible();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('calendarSelector', '.flatpickr-calendar'),
            new VisibleCSSLocator('flatpickrSelector', '.flatpickr-input'),
            new VisibleCSSLocator('dateSet', '.date-set'),
        ];
    }
}
