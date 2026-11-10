pipeline {

    agent any

    options {
        timestamps()
        skipDefaultCheckout(true)
        // Stop later stages if a publisher marks the build UNSTABLE/FAILURE mid-run
        skipStagesAfterUnstable()
    }

    environment {
        COMPOSER_ALLOW_SUPERUSER = '1'
        COMPOSER_NO_INTERACTION = '1'
        DEPLOY_TOKEN = credentials('DEPLOY_TOKEN')
        WEBSITE_ID = credentials('WEBSITE_ID')
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

                    echo "=== Verifying PHP extensions (Laravel + PhpSpreadsheet) ==="
                    php -r '
                        $required = [
                            // Laravel core
                            "ctype", "curl", "dom", "fileinfo", "filter", "hash",
                            "mbstring", "openssl", "pcre", "pdo", "session",
                            "tokenizer", "xml",
                            // Testing (sqlite in-memory)
                            "pdo_sqlite",
                            // PhpOffice / PhpSpreadsheet
                            "gd", "zip", "iconv", "simplexml",
                            "xmlreader", "xmlwriter", "zlib",
                            // Common Laravel extras
                            "bcmath", "exif",
                        ];
                        $missing = [];
                        foreach ($required as $ext) {
                            if (!extension_loaded($ext)) {
                                $missing[] = $ext;
                            }
                        }
                        if ($missing) {
                            fwrite(STDERR, "Missing required PHP extensions: " . implode(", ", $missing) . PHP_EOL);
                            fwrite(STDERR, "Image delcom-ci-php harus menyediakan ekstensi di atas (termasuk ext-gd untuk PhpSpreadsheet)." . PHP_EOL);
                            exit(1);
                        }
                        echo "OK: all required PHP extensions are available." . PHP_EOL;
                    '

                    echo "=== Installing PHP Dependencies (Composer) ==="

                    composer install \
                        --prefer-dist \
                        --no-progress \
                        --optimize-autoloader

                    echo "=== PHP Dependencies Installed ==="

                    echo "=== Composer Audit (fail on vulnerabilities) ==="
                    # Exit non-zero jika ada advisory → pipeline FAILURE
                    composer audit --no-interaction
                    echo "=== Composer Audit OK (no known vulnerabilities) ==="
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
                    # package-lock.json may not exist yet; prefer reproducible install when available
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
        // TEST (PHPUnit + Coverage)
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
                sh '''
                    set -e

                    echo "=== Re-check critical extensions before tests ==="
                    php -r '
                        foreach (["gd", "zip", "mbstring", "pdo_sqlite"] as $ext) {
                            if (!extension_loaded($ext)) {
                                fwrite(STDERR, "Missing extension for tests: {$ext}" . PHP_EOL);
                                exit(1);
                            }
                        }
                        if (!extension_loaded("pcov") && !extension_loaded("xdebug")) {
                            fwrite(STDERR, "Missing coverage driver: need pcov or xdebug" . PHP_EOL);
                            exit(1);
                        }
                        echo "OK: gd, zip, mbstring, pdo_sqlite + coverage driver ready." . PHP_EOL;
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

                    echo "=== Enforcing 100% Coverage (app/) ==="
                    php -r '
                        if (!is_file("coverage.xml")) {
                            fwrite(STDERR, "coverage.xml tidak ditemukan. Coverage wajib dihasilkan." . PHP_EOL);
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
                            fwrite(STDERR, "Metrik coverage tidak ditemukan di coverage.xml." . PHP_EOL);
                            exit(1);
                        }

                        // PHPUnit clover project metrics: statements/methods/elements
                        // (tidak selalu ada coveredclasses — jangan dipakai sebagai gate)
                        $statements = (int) $metrics["statements"];
                        $coveredStatements = (int) $metrics["coveredstatements"];
                        $methods = (int) $metrics["methods"];
                        $coveredMethods = (int) $metrics["coveredmethods"];
                        $elements = (int) $metrics["elements"];
                        $coveredElements = (int) $metrics["coveredelements"];

                        if ($statements <= 0) {
                            fwrite(STDERR, "Tidak ada statement yang diukur (statements=0)." . PHP_EOL);
                            exit(1);
                        }

                        $linePct = round(100 * $coveredStatements / $statements, 2);
                        $methodPct = $methods > 0 ? round(100 * $coveredMethods / $methods, 2) : 100.0;
                        $elementPct = $elements > 0 ? round(100 * $coveredElements / $elements, 2) : 100.0;

                        echo "Coverage summary (source=app/):" . PHP_EOL;
                        echo "  Lines/Statements : {$coveredStatements}/{$statements} ({$linePct}%)" . PHP_EOL;
                        echo "  Methods          : {$coveredMethods}/{$methods} ({$methodPct}%)" . PHP_EOL;
                        echo "  Elements         : {$coveredElements}/{$elements} ({$elementPct}%)" . PHP_EOL;

                        $failed = false;
                        if ($coveredStatements < $statements) {
                            fwrite(STDERR, "FAIL: Line coverage harus 100%. Saat ini {$linePct}%." . PHP_EOL);
                            $failed = true;
                        }
                        if ($methods > 0 && $coveredMethods < $methods) {
                            fwrite(STDERR, "FAIL: Method coverage harus 100%. Saat ini {$methodPct}%." . PHP_EOL);
                            $failed = true;
                        }
                        if ($elements > 0 && $coveredElements < $elements) {
                            fwrite(STDERR, "FAIL: Element coverage harus 100%. Saat ini {$elementPct}%." . PHP_EOL);
                            $failed = true;
                        }

                        if ($failed) {
                            exit(1);
                        }

                        echo "OK: Coverage 100% (lines, methods, elements)." . PHP_EOL;
                    '
                '''
            }

            post {
                always {
                    archiveArtifacts(
                        artifacts: 'coverage.txt,coverage.xml',
                        allowEmptyArchive: true
                    )
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
                sh '''
                    set -e

                    mkdir -p .trivy-cache || true

                    echo "======================================"
                    echo "        TRIVY SECURITY SCAN"
                    echo "======================================"

                    echo "=== Trivy Version ==="
                    trivy --version

                    echo "=== Trivy Scan ==="

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

                    echo "=== Trivy Result ==="
                    ls -lh trivy-results.sarif
                '''
            }

            post {
                always {
                    // failOnError must be false: otherwise Warnings NG can mark the
                    // whole build FAILURE while later stages still run (all green, badge red).
                    // Build failure on HIGH/CRITICAL comes from trivy --exit-code 1 above.
                    recordIssues(
                        enabledForFailure: true,
                        failOnError: false,
                        tools: [
                            sarif(
                                id: 'trivy',
                                name: 'Trivy Security',
                                pattern: 'trivy-results.sarif'
                            )
                        ]
                    )
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

                    # vendor & node_modules sengaja tidak ikut zip
                    # (diinstal ulang di server deploy via composer/npm)
                    # .env.example tetap ikut; .env lokal tidak
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
                        unzip -Z1 latest-app.zip | grep -E '^(vendor|node_modules)(/|$)' | head -n 20
                        exit 1
                    fi
                    echo "OK: vendor/ dan node_modules/ tidak ada di zip"

                    echo "=== Package Content (sample) ==="

                    unzip -l latest-app.zip | head -n 80
                '''
            }
        }

        // ============================================================
        // PUBLISH APPLICATION
        // ============================================================
        stage('Publish Application') {

            steps {

                // ========================================================
                // 1. ARCHIVE ARTIFACT KE JENKINS
                // ========================================================

                archiveArtifacts(
                    artifacts: 'latest-app.zip',
                    fingerprint: true,
                    allowEmptyArchive: false
                )

                script {

                    // ====================================================
                    // 2. BUAT IDENTITAS APPLICATION
                    // ====================================================

                    def appName = env.JOB_NAME
                        .replaceAll('[^a-zA-Z0-9._-]', '-')
                        .replaceAll('-+', '-')
                        .replaceAll('^-|-$', '')

                    def buildId = env.BUILD_NUMBER

                    echo "Application Name: ${appName}"
                    echo "Build ID: ${buildId}"

                    // ====================================================
                    // 3. COPY KE USER CONTENT
                    // ====================================================

                    sh """
                        set -e

                        echo "======================================"
                        echo "       PUBLISHING USER CONTENT"
                        echo "======================================"

                        docker exec cicd-jenkins \\
                            mkdir -p \\
                            "/var/jenkins_home/userContent/applications/${appName}/${buildId}"

                        docker cp \\
                            latest-app.zip \\
                            "cicd-jenkins:/var/jenkins_home/userContent/applications/${appName}/${buildId}/latest-app.zip"

                        echo "=== Published File ==="

                        docker exec cicd-jenkins \\
                            ls -lh \\
                            "/var/jenkins_home/userContent/applications/${appName}/${buildId}/latest-app.zip"
                    """

                    // ====================================================
                    // 4. BUAT PUBLIC ARTIFACT URL
                    // ====================================================

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
                    args '--network cicd-network'
                }
            }

            steps {
                script {

                    echo "=========================================="
                    echo "       START APPLICATION DEPLOYMENT"
                    echo "=========================================="

                    echo "Artifact URL:"
                    echo "${env.ARTIFACT_URL}"

                    // ==================================================
                    // 1. REQUEST REDEPLOYMENT
                    // ==================================================

                    echo ""
                    echo "=== Request Redeployment ==="

                    def redeployResponse = sh(
                        script: '''
                            set -e

                            curl -sS --fail-with-body \
                                -X POST "$URL_REDEPLOY" \
                                -H "Content-Type: application/json" \
                                -d "{
                                    \\"token_access\\": \\"$DEPLOY_TOKEN\\",
                                    \\"website_id\\": \\"$WEBSITE_ID\\",
                                    \\"source_url\\": \\"$ARTIFACT_URL\\",
                                    \\"source_type\\": \\"jenkins\\"
                                }"
                        ''',
                        returnStdout: true
                    ).trim()

                    echo "Redeploy Response:"
                    echo redeployResponse

                    // ==================================================
                    // 2. POLLING DEPLOYMENT PROGRESS
                    // ==================================================

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
                                    -d "{
                                        \\"token_access\\": \\"$DEPLOY_TOKEN\\",
                                        \\"website_id\\": \\"$WEBSITE_ID\\"
                                    }"
                            ''',
                            returnStdout: true
                        ).trim()

                        echo "Progress Response:"
                        echo progressResponse

                        // ==================================================
                        // PARSE JSON
                        // ==================================================

                        def json = readJSON text: progressResponse

                        deploymentStatus = json?.data?.status
                            ?.toString()
                            ?.toUpperCase()

                        if (!deploymentStatus) {
                            error(
                                "Response progress tidak memiliki data.status"
                            )
                        }

                        echo "Deployment Status: ${deploymentStatus}"

                        // ==================================================
                        // SUCCESS
                        // ==================================================

                        if (deploymentStatus == 'SUCCESS') {

                            echo ""
                            echo "=========================================="
                            echo "       ✅ DEPLOYMENT SUCCESS"
                            echo "=========================================="

                            break
                        }

                        // ==================================================
                        // FAIL
                        // ==================================================

                        if (deploymentStatus == 'FAIL') {

                            echo ""
                            echo "=========================================="
                            echo "       ❌ DEPLOYMENT FAILED"
                            echo "=========================================="

                            def deploymentLog =
                                json?.data?.log
                                    ?: 'Deployment failed tanpa log.'

                            echo ""
                            echo "========== DEPLOYMENT LOG =========="
                            echo deploymentLog
                            echo "===================================="

                            error(
                                "Deployment gagal untuk website " +
                                "${WEBSITE_ID}"
                            )
                        }

                        // ==================================================
                        // OTHER STATUS
                        // ==================================================

                        echo "Deployment masih berjalan..."


                        // ==========================================
                        // VALIDATE DEPLOYMENT CREDENTIALS
                        // ==========================================

                        if [ -z "$DEPLOY_TOKEN" ]; then
                            echo "ERROR: DEPLOY_TOKEN belum dikonfigurasi!"
                            exit 1
                        fi

                        if [ -z "$WEBSITE_ID" ]; then
                            echo "ERROR: WEBSITE_ID belum dikonfigurasi!"
                            exit 1
                        fi

                        echo "Deployment credentials berhasil ditemukan."
                    }

                    echo ""
                    echo "=========================================="
                    echo "       DEPLOYMENT FINISHED"
                    echo "=========================================="
                }
            }
        }
    }

    // ================================================================
    // POST ACTIONS
    // ================================================================
    post {

        always {
            archiveArtifacts(
                artifacts: 'trivy-results.sarif',
                allowEmptyArchive: true
            )
        }

        success {
            echo "=========================================="
            echo "       ✅ PIPELINE SUCCESS"
            echo "=========================================="
            echo "Result: ${currentBuild.currentResult}"
            echo "📦 Artifact: ${env.ARTIFACT_URL ?: '(not published)'}"
            echo "🚀 Website berhasil dideploy."
        }

        failure {
            echo "=========================================="
            echo "       ❌ PIPELINE FAILED"
            echo "=========================================="
            echo "Result: ${currentBuild.currentResult}"
            echo "Periksa log stage yang merah / Console Output untuk penyebab gagal."
        }

        unstable {
            echo "=========================================="
            echo "       ⚠️ PIPELINE UNSTABLE"
            echo "=========================================="
            echo "Result: ${currentBuild.currentResult}"
        }
    }
}