/*
 * timeago: a jQuery plugin, version: 0.9.3 (2011-01-21)
 * @requires jQuery v1.2.3 or later
 *
 * Timeago is a jQuery plugin that makes it easy to support automatically
 * updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * For usage and examples, visit:
 * http://timeago.yarp.com/
 *
 * Licensed under the MIT:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2008-2011, Ryan McGeary (ryanonjavascript -[at]- mcgeary [*dot*] org)
 */
            // Spanish
            jQuery.timeago.settings.strings = {
                prefixAgo: "Hace",
                prefixFromNow: "Dentro de",
                suffixAgo: "",
                suffixFromNow: "",
                seconds: "menos de un minuto",
                minute: "un minuto",
                minutes: "unos %d minutos",
                hour: "una hora",
                hours: "%d horas",
                day: "un día",
                days: "%d días",
                month: "un mes",
                months: "%d meses",
                year: "un año",
                years: "%d años"
                };
            jQuery(document).ready(function() {
                 jQuery("abbr.timeago").timeago();
            });
