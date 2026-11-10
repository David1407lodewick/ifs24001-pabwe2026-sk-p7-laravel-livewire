
pipeline {

    agent any

    options {
        timestamps()
        skipDefaultCheckout(true)
        skipStagesAfterUnstable()
    }

    environment {
        COMPOSER_ALLOW_SUPERUSER = '1'
        COMPOSER_NO_INTERACTION = '1'

        URL_PROGRESS = 'https://delcom.org/api/dws/websites/progress'
        URL_REDEPLOY = 'https://delcom.org/api/dws/websites/redeploy'

    }

    stages {

        // ============================================================
        // CHECKOUT
        // ============================================================
        stage('Checkout') {
            agent {
                docker {
                    image 'alpine:3.21'
                    reuseNode true
                }
            }

            steps {
                checkout scm
            }
        }

        // ============================================================
        // INSTALL PHP DEPENDENCIES
        // ============================================================
        stage('Install PHP Dependencies') {
            agent {
                docker {
                    image 'delcom-ci-php:8.5'
                    reuseNode true
                    args '-u root'
                }
            }

            steps {
                sh '''
                    set -e

                    echo "=== PHP Runtime ==="
                    php -v

                    echo "=== Verifying PHP extensions ==="

                    php -r '
                        $required = [
                            "ctype", "curl", "dom", "fileinfo", "filter", "hash",
                            "mbstring", "openssl", "pcre", "pdo", "session",
                            "tokenizer", "xml",
                            "pdo_sqlite",
                            "gd", "zip", "iconv", "simplexml",
                            "xmlreader", "xmlwriter", "zlib",
                            "bcmath", "exif",
                        ];

                        $missing = [];

                        foreach ($required as $ext) {
                            if (!extension_loaded($ext)) {
                                $missing[] = $ext;
                            }
                        }

                        if ($missing) {
                            fwrite(
                                STDERR,
                                "Missing PHP extensions: " .
                                implode(", ", $missing) . PHP_EOL
                            );
                            exit(1);
                        }

                        echo "OK: all required PHP extensions are available." . PHP_EOL;
                    '

                    echo "=== Installing PHP Dependencies ==="

                    composer install \
                        --prefer-dist \
                        --no-progress \
                        --optimize-autoloader

                    echo "=== PHP Dependencies Installed ==="

                    echo "=== Composer Audit ==="

                    composer audit --no-interaction

                    echo "=== Composer Audit OK ==="
                '''
            }
        }

        // ============================================================
        // INSTALL FRONTEND DEPENDENCIES + BUILD ASSETS
        // ============================================================
        stage('Install & Build Frontend') {
            agent {
                docker {
                    image 'node:24-alpine'
                    reuseNode true
                }
            }

            steps {
                sh '''
                    set -e

                    echo "=== Installing Frontend Dependencies ==="

                    if [ -f package-lock.json ]; then
                        npm ci --ignore-scripts
                    else
                        npm install --ignore-scripts
                    fi

                    echo "=== Building Vite Assets ==="

                    npm run build

                    echo "=== Frontend Build Completed ==="
                '''
            }
        }

        // ============================================================
        // TEST (PHPUNIT + COVERAGE)
        // ============================================================
        stage('Test') {
            agent {
                docker {
                    image 'delcom-ci-php:8.5'
                    reuseNode true
                    args '-u root'
                }
            }

            steps {
                script {

                    int testExit = sh(
                        script: '''
                            set -e

                            echo "=== Re-check critical extensions ==="

                            php -r '
                                foreach (["gd", "zip", "mbstring", "pdo_sqlite"] as $ext) {
                                    if (!extension_loaded($ext)) {
                                        fwrite(STDERR, "Missing extension: {$ext}" . PHP_EOL);
                                        exit(1);
                                    }
                                }

                                if (!extension_loaded("pcov") && !extension_loaded("xdebug")) {
                                    fwrite(STDERR, "Missing coverage driver: need pcov or xdebug" . PHP_EOL);
                                    exit(1);
                                }

                                echo "OK: PHP extensions and coverage driver ready." . PHP_EOL;
                            '

                            echo "=== Preparing Laravel Testing Environment ==="

                            if [ ! -f .env ]; then
                                cp .env.example .env
                            fi

                            php artisan key:generate --force
                            php artisan config:clear
                            php artisan view:clear

                            echo "=== Running PHPUnit with Coverage ==="

                            php vendor/bin/phpunit \
                                --colors=never \
                                --coverage-text=coverage.txt \
                                --coverage-clover=coverage.xml \
                                --coverage-html=coverage-html

                            echo "=== Tests Passed ==="

                            ls -lh coverage.xml coverage.txt || true

                            echo "=== Enforcing 100% Coverage ==="

                            php -r '
                                if (!is_file("coverage.xml")) {
                                    fwrite(STDERR, "coverage.xml tidak ditemukan." . PHP_EOL);
                                    exit(1);
                                }

                                $xml = @simplexml_load_file("coverage.xml");

                                if ($xml === false) {
                                    fwrite(STDERR, "coverage.xml tidak dapat diparse." . PHP_EOL);
                                    exit(1);
                                }

                                $metrics = $xml->project->metrics ?? null;

                                if ($metrics === null) {
                                    $nodes = $xml->xpath("//project/metrics");
                                    $metrics = $nodes[0] ?? null;
                                }

                                if ($metrics === null) {
                                    fwrite(STDERR, "Metrik coverage tidak ditemukan." . PHP_EOL);
                                    exit(1);
                                }

                                $statements = (int) $metrics["statements"];
                                $coveredStatements = (int) $metrics["coveredstatements"];
                                $methods = (int) $metrics["methods"];
                                $coveredMethods = (int) $metrics["coveredmethods"];
                                $elements = (int) $metrics["elements"];
                                $coveredElements = (int) $metrics["coveredelements"];

                                if ($statements <= 0) {
                                    fwrite(STDERR, "Tidak ada statement yang diukur." . PHP_EOL);
                                    exit(1);
                                }

                                $linePct = round(100 * $coveredStatements / $statements, 2);

                                $methodPct = $methods > 0
                                    ? round(100 * $coveredMethods / $methods, 2)
                                    : 100.0;

                                $elementPct = $elements > 0
                                    ? round(100 * $coveredElements / $elements, 2)
                                    : 100.0;

                                echo "Coverage summary (source=app/):" . PHP_EOL;
                                echo "Lines/Statements: {$coveredStatements}/{$statements} ({$linePct}%)" . PHP_EOL;
                                echo "Methods: {$coveredMethods}/{$methods} ({$methodPct}%)" . PHP_EOL;
                                echo "Elements: {$coveredElements}/{$elements} ({$elementPct}%)" . PHP_EOL;

                                $failed = false;

                                if ($coveredStatements < $statements) {
                                    fwrite(STDERR, "FAIL: Line coverage harus 100%." . PHP_EOL);
                                    $failed = true;
                                }

                                if ($methods > 0 && $coveredMethods < $methods) {
                                    fwrite(STDERR, "FAIL: Method coverage harus 100%." . PHP_EOL);
                                    $failed = true;
                                }

                                if ($elements > 0 && $coveredElements < $elements) {
                                    fwrite(STDERR, "FAIL: Element coverage harus 100%." . PHP_EOL);
                                    $failed = true;
                                }

                                if ($failed) {
                                    exit(1);
                                }

                                echo "OK: Coverage 100%." . PHP_EOL;
                            '
                        ''',
                        returnStatus: true
                    )

                    echo "=== Archive Coverage Reports ==="

                    archiveArtifacts(
                        artifacts: 'coverage.txt,coverage.xml',
                        allowEmptyArchive: true,
                        fingerprint: true
                    )

                    if (testExit != 0) {
                        error("PHPUnit atau pemeriksaan coverage gagal.")
                    }

                    echo "=== Test and Coverage Completed ==="
                }
            }
        }

        // ============================================================
        // TRIVY SECURITY SCAN
        // ============================================================
        stage('Trivy Security Scan') {
            agent {
                docker {
                    image 'aquasec/trivy:0.74.0'
                    reuseNode true
                    args '''
                        --entrypoint=""
                        -e HOME=/tmp
                        -e XDG_CACHE_HOME=/tmp/.cache
                    '''
                }
            }

            steps {
                script {

                    int trivyExit = sh(
                        script: '''
                            set +e

                            mkdir -p .trivy-cache

                            echo "======================================"
                            echo "        TRIVY SECURITY SCAN"
                            echo "======================================"

                            trivy --version

                            trivy fs \
                                --cache-dir .trivy-cache \
                                --scanners vuln \
                                --severity HIGH,CRITICAL \
                                --skip-dirs vendor \
                                --skip-dirs node_modules \
                                --skip-dirs coverage-html \
                                --skip-dirs storage \
                                --skip-dirs bootstrap/cache \
                                --format sarif \
                                --output trivy-results.sarif \
                                --exit-code 1 \
                                .

                            TRIVY_EXIT=$?

                            echo "=== Trivy Result ==="

                            if [ -f trivy-results.sarif ]; then
                                ls -lh trivy-results.sarif
                            else
                                echo "WARNING: trivy-results.sarif tidak ditemukan."
                            fi

                            exit "$TRIVY_EXIT"
                        ''',
                        returnStatus: true
                    )

                    echo "=== Archive Trivy Report ==="

                    archiveArtifacts(
                        artifacts: 'trivy-results.sarif',
                        allowEmptyArchive: true,
                        fingerprint: true
                    )

                    if (trivyExit != 0) {
                        error(
                            "Trivy Security Scan gagal atau menemukan vulnerability HIGH/CRITICAL."
                        )
                    }

                    echo "=== Trivy Security Scan Completed ==="
                }
            }
        }

        // ============================================================
        // PACKAGE APPLICATION
        // ============================================================
        stage('Package Application') {
            agent {
                docker {
                    image 'delcom-ci-php:8.5'
                    reuseNode true
                    args '-u root'
                }
            }

            steps {
                sh '''
                    set -e

                    echo "======================================"
                    echo "       CREATING APPLICATION PACKAGE"
                    echo "======================================"

                    rm -f latest-app.zip

                    zip -r latest-app.zip . \
                        -x "vendor/*" \
                        -x "node_modules/*" \
                        -x ".git/*" \
                        -x ".env" \
                        -x ".env.backup" \
                        -x ".env.production" \
                        -x ".env.local" \
                        -x "coverage/*" \
                        -x "coverage-html/*" \
                        -x "coverage.txt" \
                        -x "coverage.xml" \
                        -x ".trivy-cache/*" \
                        -x "latest-app.zip" \
                        -x "trivy-results.sarif" \
                        -x ".docs/*" \
                        -x "tests/*" \
                        -x "storage/logs/*" \
                        -x "storage/framework/cache/data/*" \
                        -x "storage/framework/sessions/*" \
                        -x "storage/framework/views/*" \
                        -x "storage/framework/testing/*" \
                        -x "bootstrap/cache/*.php"

                    echo "=== Application Package Created ==="

                    ls -lh latest-app.zip

                    echo "=== Verify vendor/node_modules excluded ==="

                    if unzip -Z1 latest-app.zip | grep -E '^(vendor|node_modules)(/|$)' >/dev/null; then
                        echo "ERROR: zip masih berisi vendor/ atau node_modules/"
                        exit 1
                    fi

                    echo "OK: vendor/ dan node_modules/ tidak ada di zip"

                    unzip -l latest-app.zip | head -n 80
                '''
            }
        }

        // ============================================================
        // PUBLISH APPLICATION
        // ============================================================
        stage('Publish Application') {
            steps {

                script {

                    echo "=== Archive Application Package ==="

                    archiveArtifacts(
                        artifacts: 'latest-app.zip',
                        fingerprint: true,
                        allowEmptyArchive: false
                    )

                    def appName = env.JOB_NAME
                        .replaceAll('[^a-zA-Z0-9._-]', '-')
                        .replaceAll('-+', '-')
                        .replaceAll('^-|-$', '')

                    def buildId = env.BUILD_NUMBER

                    echo "Application Name: ${appName}"
                    echo "Build ID: ${buildId}"

                    sh """
                        set -e

                        echo "======================================"
                        echo "       PUBLISHING USER CONTENT"
                        echo "======================================"

                        docker exec cicd-jenkins \
                            mkdir -p \
                            "/var/jenkins_home/userContent/applications/${appName}/${buildId}"

                        docker cp \
                            latest-app.zip \
                            "cicd-jenkins:/var/jenkins_home/userContent/applications/${appName}/${buildId}/latest-app.zip"

                        echo "=== Published File ==="

                        docker exec cicd-jenkins \
                            ls -lh \
                            "/var/jenkins_home/userContent/applications/${appName}/${buildId}/latest-app.zip"
                    """

                    def jenkinsBaseUrl = env.BUILD_URL
                        .substring(0, env.BUILD_URL.indexOf('/job/'))
                        .replace('localhost', 'host.docker.internal')

                    env.ARTIFACT_URL =
                        "${jenkinsBaseUrl}/userContent/applications/${appName}/${buildId}/latest-app.zip"

                    echo "======================================"
                    echo "       APPLICATION PUBLISHED"
                    echo "======================================"

                    echo "Artifact URL:"
                    echo "${env.ARTIFACT_URL}"
                }
            }
        }

        // ============================================================
        // DEPLOY APPLICATION
        // ============================================================
        stage('Deploy Application') {
            agent {
                docker {
                    image 'curlimages/curl:8.15.0'
                    reuseNode true
                }
            }

            steps {
                script {

                    echo ""
                    echo "=========================================="
                    echo "       START APPLICATION DEPLOYMENT"
                    echo "=========================================="

                    // Validate deployment configuration

                    if (!env.DEPLOY_TOKEN?.trim()) {
                        error("DEPLOY_TOKEN belum dikonfigurasi di Jenkins!")
                    }

                    if (!env.WEBSITE_ID?.trim()) {
                        error("WEBSITE_ID belum dikonfigurasi di Jenkins!")
                    }

                    if (!env.URL_REDEPLOY?.trim()) {
                        error("URL_REDEPLOY belum dikonfigurasi!")
                    }

                    if (!env.URL_PROGRESS?.trim()) {
                        error("URL_PROGRESS belum dikonfigurasi!")
                    }

                    if (!env.ARTIFACT_URL?.trim()) {
                        error("ARTIFACT_URL belum tersedia!")
                    }

                    echo "Deployment configuration berhasil diperiksa."

                    // Request redeployment

                    echo ""
                    echo "=== Request Redeployment ==="

                    def redeployResponse = sh(
                        script: '''
                            set -e

                            curl -sS --fail-with-body \
                                -X POST "$URL_REDEPLOY" \
                                -H "Content-Type: application/json" \
                                --data-binary @- <<EOF
{
    "token_access": "$DEPLOY_TOKEN",
    "website_id": "$WEBSITE_ID",
    "source_url": "$ARTIFACT_URL",
    "source_type": "jenkins"
}
EOF
                        ''',
                        returnStdout: true
                    ).trim()

                    echo "Redeploy Response:"
                    echo redeployResponse

                    // Polling deployment progress

                    echo ""
                    echo "=== Waiting For Deployment ==="

                    def maxAttempts = 120
                    def attempt = 0
                    def deploymentStatus = 'IN_PROGRESS'

                    while (deploymentStatus == 'IN_PROGRESS') {

                        attempt++

                        if (attempt > maxAttempts) {
                            error(
                                "Deployment timeout. " +
                                "Status masih IN_PROGRESS setelah " +
                                "${maxAttempts} attempts."
                            )
                        }

                        sleep time: 5, unit: 'SECONDS'

                        echo ""
                        echo "=== Checking Deployment Progress (${attempt}/${maxAttempts}) ==="

                        def progressResponse = sh(
                            script: '''
                                set -e

                                curl -sS --fail-with-body \
                                    -X POST "$URL_PROGRESS" \
                                    -H "Content-Type: application/json" \
                                    --data-binary @- <<EOF
{
    "token_access": "$DEPLOY_TOKEN",
    "website_id": "$WEBSITE_ID"
}
EOF
                            ''',
                            returnStdout: true
                        ).trim()

                        echo "Progress Response:"
                        echo progressResponse

                        def progressJson = readJSON(
                            text: progressResponse
                        )

                        deploymentStatus = progressJson?.data?.status
                            ?.toString()
                            ?.toUpperCase()

                        if (!deploymentStatus) {
                            error(
                                "Response progress tidak memiliki data.status"
                            )
                        }

                        echo "Deployment Status: ${deploymentStatus}"

                        // Deployment berhasil

                        if (deploymentStatus == 'SUCCESS') {

                            echo ""
                            echo "=========================================="
                            echo "       DEPLOYMENT SUCCESS"
                            echo "=========================================="

                            break
                        }

                        // Deployment gagal

                        if (deploymentStatus == 'FAIL') {

                            echo ""
                            echo "=========================================="
                            echo "       DEPLOYMENT FAILED"
                            echo "=========================================="

                            def deploymentLog =
                                progressJson?.data?.log
                                    ?: 'Deployment failed tanpa log.'

                            echo ""
                            echo "========== DEPLOYMENT LOG =========="
                            echo deploymentLog
                            echo "===================================="

                            error(
                                "Deployment gagal untuk website " +
                                "${env.WEBSITE_ID}"
                            )
                        }

                        // Status tidak dikenal

                        if (deploymentStatus != 'IN_PROGRESS') {
                            error(
                                "Status deployment tidak dikenal: " +
                                "${deploymentStatus}"
                            )
                        }

                        echo "Deployment masih berjalan..."
                    }

                    // Deployment finished

                    echo ""
                    echo "=========================================="
                    echo "       DEPLOYMENT FINISHED"
                    echo "=========================================="

                    echo "Website ID: ${env.WEBSITE_ID}"
                    echo "Artifact: ${env.ARTIFACT_URL}"
                }
            }
        }
    }

    // ================================================================
    // POST ACTIONS
    // ================================================================
    post {

        success {
            echo "=========================================="
            echo "       PIPELINE SUCCESS"
            echo "=========================================="

            echo "Result: ${currentBuild.currentResult}"
            echo "Artifact: ${env.ARTIFACT_URL ?: '(not published)'}"
            echo "Website berhasil dideploy."
        }

        failure {
            echo "=========================================="
            echo "       PIPELINE FAILED"
            echo "=========================================="

            echo "Result: ${currentBuild.currentResult}"
            echo "Periksa log stage yang merah / Console Output."
        }

        unstable {
            echo "=========================================="
            echo "       PIPELINE UNSTABLE"
            echo "=========================================="

            echo "Result: ${currentBuild.currentResult}"
        }
    }
}
