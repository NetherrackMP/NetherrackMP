import {dirname, join} from "path";
import {fileURLToPath, pathToFileURL} from "url";
import {iniToJSON} from "./utils.js";
import {readdirSync} from "fs";
import {argv} from "node:process";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const opts = {};
const aliases = {
	L: "loop",
	P: "no-phar"
};
argv.slice(2).filter(i => i.startsWith("-")).forEach(i => {
	if (i[1] !== "-") i = "-" + i;
	i = i.substring(2).split("=");
	i[0] = aliases[i[0]] || i[0];
	opts[i[0]] = i.length === 1 ? true : i.slice(1).join("=");
});
const isTerminal = __filename === argv[1] || __filename === argv[1] + ".js" || __dirname === argv[1];

global.DIR = __dirname;
global.LOCALE_PATH = join(__dirname, "../src/lang/locale");
global.MAIN_LOCALE_NAME = "en_us.ini";
global.MAIN_LOCALE = iniToJSON(join(LOCALE_PATH, MAIN_LOCALE_NAME));


export default async function build() {
	const ST = Date.now();
	const tasks = new Set(
		readdirSync(join(__dirname, "tasks"))
			.filter(i => i.endsWith(".js"))
			.map(i => i.substring(0, i.length - 3))
	);
	if (opts["no-phar"]) tasks.delete("phar");
	let done = 0;
	await Promise.all([...tasks].map(async i => {
		const T = Date.now();
		const path = pathToFileURL(join(DIR, "tasks", i + ".js"));
		const task = await import(path.href);
		await task.default(text => {
			console.log(`Build failed at the "${task.name}" task!`);
			console.error(text);
			process.exit();
		});
		console.log(`(${++done}/${tasks.size}) ${task.name} (${Date.now() - T} ms)`);
	}));
	console.log("Done (" + (Date.now() - ST) + "ms)");
};
if (isTerminal) {
	if (opts["loop"]) {
		const loop = async () => {
			console.clear();
			await build();
			setTimeout(loop, opts["loop"] === true ? 5000 : (opts["loop"] * 1) || 5000);
		};
		loop().then(r => r);
	} else await build();
}