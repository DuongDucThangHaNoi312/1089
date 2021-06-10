<?php

namespace App\Service;

class JobSeekerRegistrationSource {
    public static function heading($source) {
        $description = 'Take the stress out of your job search';
        $format = '%s by creating an account';
        switch ($source) {
            case 'view_city_profile':
                $description = "Create an account to view City Profiles";
                break;
            case 'view_city_link':
                $description = "Register or <a class='job-registration-step-one btn-login' href='/login'>Login</a> to use Links";
                break;
            case 'save_job_title':
                $description = sprintf($format, 'Save jobs');
                break;
            case 'view_job_alert':
                $description = sprintf($format, 'Access job alerts');
                break;
            case 'save_job_alert':
                $description = sprintf($format, 'Save job alerts');
                break;
            case 'submit_interest':
                $description = sprintf($format, 'Submit interest in jobs');
                break;
            case 'view_job_alert_apply_link':
                $description = sprintf('%s',
                    'Register or <a class="btn-login" href="/login">Login</a> to Link to Job Application Portals');
                break;
            case 'link_to_job':
                $description = sprintf('%s', 'Register or <a class="btn-login" href="/login">Login</a> to Link to Job');
                break;
            default:
                break;
        }

        return $description;
    }

    public static function description($source) {
        $description = 'Join others in finding your dream city government career today!';
        $format = '%s';
        switch ($source) {
            case 'view_city_profile':
                $description = "City Profiles include Contact Info, Links and other helpful info organized in one place";
                break;
            case 'view_city_link':
                $description = "Save time and view jobs at all cities in each county served, and quickly access salary tables and other information.";
                break;
            case 'save_job_title':
                $description = sprintf($format, 'Saving jobs to your account lets you take action at a later time.');
                break;
            case 'view_job_alert':
                $description = sprintf($format, 'Avoid visiting multiple city job boards, access all job alerts in one place.');
                break;
            case 'save_job_alert':
                $description = sprintf($format, 'Saving job alerts to your account lets you quickly reference them at a later time.');
                break;
            case 'submit_interest':
                $description = sprintf($format, 'Save time and get alerts of jobs you\'ve submitted interest in.');
                break;
            case 'view_job_alert_apply_link':
                $description = sprintf($format, 'We\'re Happy We Helped You Find a Job You Want to Apply For!');
                break;
            default:
                break;
        }

        return $description;
    }

    public static function button($source) {
        $description = 'Begin your job search...';
        $format = 'Continue to %s';
        switch ($source) {
            case 'view_city_profile':
                $description = sprintf($format, 'view this city profile...');
                break;
            case 'view_city_link':
                $description = sprintf('%s', 'Try it for FREE NOW');
                break;
            case 'save_job_title':
                $description = sprintf($format, 'save this job...');
                break;
            case 'view_job_alert':
                $description = sprintf($format, 'view this job alert...');
                break;
            case 'save_job_alert':
                $description = sprintf($format, 'save this job alert...');
                break;
            case 'submit_interest':
                $description = sprintf($format, 'submit interest...');
                break;
            case 'view_job_alert_apply_link':
                $description = sprintf('%s', 'Try it for FREE NOW');
                break;
            default:
                break;
        }

        return $description;
    }
}
