import chalk from 'chalk';
import boxen from 'boxen';
import { spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

class OutputFormatter {
    constructor() {
        this.logFiles = {
            debug: 'log/debug.log',
            error: 'log/e.log',
            app: 'log/app.log',
            info: 'log/info.log'
        };
        this.logWatchers = new Map();
        this.filePositions = new Map();
        this.setupLogWatchers();
    }

    setupLogWatchers() {
        Object.entries(this.logFiles).forEach(([type, filePath]) => {
            this.watchLogFile(type, filePath);
        });
    }

    watchLogFile(type, filePath) {
        if (!fs.existsSync(filePath)) {
            ensureDirForFile(filePath);
            fs.writeFileSync(filePath, '');
        }

        try {
            const stats = fs.statSync(filePath);
            this.filePositions.set(filePath, stats.size);
        } catch (error) {
            this.filePositions.set(filePath, 0);
        }

        const watcher = fs.watch(filePath, (eventType) => {
            if (eventType === 'change') {
                this.readAndDisplayLog(type, filePath);
            }
        });

        this.logWatchers.set(type, watcher);
    }

    readAndDisplayLog(type, filePath) {
        try {
            const stats = fs.statSync(filePath);
            if (stats.size === 0) return;

            const currentPosition = this.filePositions.get(filePath) || 0;

            if (stats.size < currentPosition) {
                this.filePositions.set(filePath, 0);
                return;
            }
            const stream = fs.createReadStream(filePath, {
                start: currentPosition,
                encoding: 'utf8'
            });

            let buffer = '';
            stream.on('data', (chunk) => {
                buffer += chunk;
            });

            stream.on('end', () => {
                if (buffer.trim()) {
                    const lines = buffer.split('\n').filter(line => line.trim());
                    lines.forEach(line => {
                        if (line.trim()) {
                            const formattedLine = this.formatLogLine(type, line);
                            if (formattedLine) {
                                process.stdout.write(formattedLine + '\n');
                            }
                        }
                    });
                }
                this.filePositions.set(filePath, stats.size);
            });

            stream.on('error', (error) => null);

        } catch (error) {
            // Ignoruj chyby při čtení log
        }
    }

    formatLogLine(type, line) {
        const timestamp = new Date().toLocaleTimeString();

        switch (type) {
            case 'debug':
                return `${chalk.blue.bold('[DEBUG]')} ${chalk.blue(line)}`;
            case 'error':
                return `${chalk.red.bold('[ERROR]')} ${chalk.red(line)}`;
            case 'app':
                return `${chalk.green.bold('[APP]')} ${chalk.green(line)}`;
            case 'info':
                return `${chalk.cyan.bold('[INFO]')} ${chalk.cyan(line)}`;
            default:
                return `${chalk.gray.bold('[LOG]')} ${chalk.gray(line)}`;
        }
    }

    formatHeader() {
        const textToDisplay = `
        ${chalk.cyan.bold('Arcadia CLI v1.0.0')}
        ${chalk.gray('A modern PHP framework for building web applications.')}
        ${chalk.gray('Visit us at: https://arcadia.dev')}
        ${chalk.gray('Documentation: https://docs.arcadia.dev')}

        ${chalk.gray('Starting development server...')}
        ${chalk.gray('You can access your application at:')}
        ${chalk.gray('Tailwind CSS is being processed...')}
        ${chalk.gray('Vite is running...')}
        ${chalk.green.bold('http://localhost:8000')}

        ${chalk.gray('Log files being monitored:')}
        ${chalk.blue('  • log/debug.log')}
        ${chalk.red('  • log/e.log')}
        ${chalk.green('  • log/app.log')}
        ${chalk.cyan('  • log/info.log')}

        ${chalk.gray('Press Ctrl+C to stop the server.')}
        ${chalk.gray('Type :c and press Enter to clear all logs.')}

        ${chalk.gray('For help, visit: https://arcadia.dev/help')}
        ${chalk.gray('Enjoy your development!')}`;

        return boxen(textToDisplay, {
            padding: 1,
            margin: 1,
            borderStyle: 'double',
        });
    }

    parsePHPOutput(data) {
        const text = data.toString().trim();

        const requestRegex = /\[(.+?)]\s+(\d+\.\d+\.\d+\.\d+):(\d+)\s+\[(\d+)]:\s+(\w+)\s+(.+)/;
        const connectRegex = /\[(.+?)]\s+(\d+\.\d+\.\d+\.\d+):(\d+)\s+(Accepted|Closing)/;

        const requestMatch = text.match(requestRegex);
        if (requestMatch) {
            const [, timestamp, ip, port, status, method, pathStr] = requestMatch;
            const color = status.startsWith('2')
                ? chalk.green
                : status.startsWith('3')
                    ? chalk.yellow
                    : chalk.red;

            return `${chalk.white.bold(timestamp)} ${chalk.dim(method)} ${chalk.white(pathStr)} ${color(`[${status}]`)}`;
        }

        const connectMatch = text.match(connectRegex);
        if (connectMatch) {
            const [, timestamp, ip, port, action] = connectMatch;
            const color = action === 'Accepted' ? chalk.green : chalk.gray;
            return null;
            return `${chalk.white.bold(timestamp)} ${chalk.dim(`${ip}:${port}`)} ${color(action)}`;
        }

        if (text.includes('PHP Warning') || text.includes('PHP Notice')) {
            return chalk.yellow(text);
        }
        if (text.includes('PHP Fatal')) {
            return chalk.red(text);
        }

        return chalk.dim(text);
    }

    parseTailwindOutput(data) {
        return chalk.magenta(data.toString());
    }

    parseViteOutput(data) {
        return chalk.cyan(data.toString());
    }
}

function ensureDirForFile(filePath) {
    const dir = path.dirname(filePath);
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

function clearAllLogs(formatter) {
    Object.values(formatter.logFiles).forEach(filePath => {
        try {
            fs.writeFileSync(filePath, '');
            // Resetuj pozici v souboru
            formatter.filePositions.set(filePath, 0);
            console.log(chalk.gray(`  • Cleared ${filePath}`));
        } catch (error) {
            console.log(chalk.red(`  • Failed to clear ${filePath}: ${error.message}`));
        }
    });
}

function startPHPServer(port, formatter, spawnOptions, processes, logFile) {
    const php = spawn('php', [
        '-S', `localhost:${port}`,
        '-t', 'public',
        // Logování chyb do souboru + ponechat přísnost
        '-d', 'error_reporting=E_ALL',
        '-d', 'display_errors=1',
        '-d', 'log_errors=On',
        '-d', `error_log=${logFile}`,
        '-d', 'memory_limit=256M',
    ], spawnOptions);

    processes.push(php);

    // Stream pro "tee" do logu
    const logStream = fs.createWriteStream(logFile, { flags: 'a' });

    const handleOut = (buf, isErr = false) => {
        const line = formatter.parsePHPOutput(buf);
        if (line) process.stdout.write(line + '\n');
        // do souboru ukládáme surový výstup (bez barev)
        logStream.write(buf.toString());
    };

    // php.stdout.setEncoding('utf8');
    // php.stdout.on('data', (data) => handleOut(data, false));

    php.stderr.setEncoding('utf8');
    php.stderr.on('data', (data) => handleOut(data, true));

    php.on('close', () => logStream.end());
}

function startServer() {
    const formatter = new OutputFormatter();
    clearAllLogs(formatter);
    console.log(formatter.formatHeader());

    const spawnOptions = {
        stdio: 'pipe',
        env: { ...process.env, FORCE_COLOR: '3' },
    };

    const processes = [];

    // Handle command input
    process.stdin.setEncoding('utf8');
    process.stdin.setRawMode(true);
    process.stdin.resume();

    let commandBuffer = '';
    process.stdin.on('data', (key) => {
        // Ctrl+C to exit
        if (key === '\u0003') {
            stopAll();
            return;
        }

        // Enter key
        if (key === '\r' || key === '\n') {
            if (commandBuffer.trim() === ':c') {
                console.clear()
                clearAllLogs(formatter);
                console.log(chalk.green.bold('\n[COMMAND] All log files cleared!'));
            }
            commandBuffer = '';
            return;
        }

        // Backspace
        if (key === '\u007f') {
            commandBuffer = commandBuffer.slice(0, -1);
            return;
        }

        // Add character to buffer
        commandBuffer += key;
    });

    // Cesta k logu PHP (env proměnná má přednost)
    const phpErrorLog = process.env.PHP_ERROR_LOG
        ? path.resolve(process.env.PHP_ERROR_LOG)
        : path.resolve('storage/logs/php_errors.log');

    ensureDirForFile(phpErrorLog);

    // PHP server(y)
    const workerPorts = [8000];
    workerPorts.forEach(port => {
        startPHPServer(port, formatter, spawnOptions, processes, phpErrorLog);
        console.log(chalk.green(`PHP Server started on port ${port}`));
        console.log(chalk.gray(`PHP error_log: ${phpErrorLog}`));
    });

    // Vite
    const vite = spawn('vite', [], spawnOptions);
    processes.push(vite);
    let viteCounter = 0;
    vite.stdout.setEncoding('utf8');
    vite.stderr.on('data', data => {
        viteCounter++;
        if (viteCounter !== 2 || data.toString().includes('Vite server running at')) {
            process.stderr.write(formatter.parseViteOutput(data));
        }
        console.log();
    });
    vite.stderr.on('data', data => process.stderr.write(chalk.red(data.toString())));

    // Tailwind
    const tailwind = spawn('npx', [
        '@tailwindcss/cli',
        '-i', 'resources/css/_app.css',
        '-o', 'resources/assets/app.css',
        '--watch',
        '--optimize',
        '-m'
    ], spawnOptions);
    processes.push(tailwind);

        // Clean exit
    const stopAll = () => {
        console.log(chalk.gray('\nShutting down...'));

        // Ukonči stdin
        process.stdin.setRawMode(false);
        process.stdin.pause();

        // Ukonči log watchers
        formatter.logWatchers.forEach(watcher => {
            watcher.close();
        });

        processes.forEach(p => p.kill());
        process.exit();
    };
    process.on('SIGINT', stopAll);
    process.on('SIGTERM', stopAll);
}

startServer();
export { OutputFormatter, startServer };
