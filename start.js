import chalk from 'chalk';
import boxen from 'boxen';
import { spawn } from 'child_process';

class OutputFormatter {
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

        ${chalk.gray('Press Ctrl+C to stop the server.')}

        ${chalk.gray('For help, visit: https://arcadia.dev/help')}
        ${chalk.gray('Enjoy your development!')}`

        return boxen(
            textToDisplay,
          {
            padding: 1,
            margin: 1,
            borderStyle: 'double',
        });
    }

    parsePHPOutput(data) {
        const text = data.toString().trim();

        const requestRegex = /\[(.+?)\]\s+(\d+\.\d+\.\d+\.\d+):(\d+)\s+\[(\d+)\]:\s+(\w+)\s+(.+)/;
        const connectRegex = /\[(.+?)\]\s+(\d+\.\d+\.\d+\.\d+):(\d+)\s+(Accepted|Closing)/;

        const requestMatch = text.match(requestRegex);
        if (requestMatch) {
            const [, timestamp, ip, port, status, method, path] = requestMatch;
            const color = status.startsWith('2') ? chalk.green :
                        status.startsWith('3') ? chalk.yellow :
                        chalk.red;

            return `${chalk.white.bold(timestamp)} ${chalk.dim(method)} ${chalk.white(path)} ${color(`[${status}]`)}`;
        }

        const connectMatch = text.match(connectRegex);
        if (connectMatch) {
            const [, timestamp, ip, port, action] = connectMatch;
            const color = action === 'Accepted' ? chalk.green : chalk.gray;

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

function startPHPServer(port, formatter, spawnOptions, processes) {
    const php = spawn('php', [
        '-S', `localhost:${port}`,
        '-t', 'public',
        '-d', 'error_reporting=E_ALL',
        '-d', 'display_errors=1',
        '-d', 'memory_limit=256M'
    ], spawnOptions);

    processes.push(php);
    php.stdout.setEncoding('utf8');
    php.stderr.on('data', data => {
        const out = formatter.parsePHPOutput(data);
        if (out) process.stdout.write(out + '\n');
    });
}

function startServer() {
    const formatter = new OutputFormatter();
    console.log(formatter.formatHeader());

    const spawnOptions = {
        stdio: 'pipe',
        env: { ...process.env, FORCE_COLOR: '3' },
    };

    let viteCounter = 0;

    const processes = [];

    // Multiple PHP Servers
    const workerPorts = [8000];

    workerPorts.forEach(port => {
        startPHPServer(port, formatter, spawnOptions, processes);
        console.log(chalk.green(`PHP Server started on port ${port}`));
    });

    // Vite
    const vite = spawn('vite', [], spawnOptions);
    processes.push(vite);
    vite.stdout.setEncoding('utf8');
    vite.stdout.on('data', data => {
      viteCounter++;
      if (viteCounter != 2 || data.toString().includes('Vite server running at')) {
        process.stdout.write(formatter.parseViteOutput(data))
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
        processes.forEach(p => p.kill());
        process.exit();
    };
    process.on('SIGINT', stopAll);
    process.on('SIGTERM', stopAll);
}

startServer();
export { OutputFormatter, startServer };
