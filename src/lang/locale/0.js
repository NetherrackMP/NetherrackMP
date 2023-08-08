const fs = require("fs");
const path = require("path");
const MAIN = "eng.ini";
const files = fs.readdirSync(__dirname).filter(i => i.endsWith(".ini") && i !== MAIN);

const iniToJSON = file => {
	const content = fs.readFileSync(path.join(__dirname, file), "utf8");
	const obj = {};
	content.split("\n")
		.filter(i => /[a-zA-Z]/.test(i[0]))
		.forEach(i => obj[i.split("=")[0]] = i.split("=").slice(1).join("="));
	return obj;
};
const main = iniToJSON(MAIN);

for (const file of files) {
	const ini = iniToJSON(file);
	const content = [];
	for (const key in main) content.push([key.trim(), (ini[key] || main[key]).trim()]);
	fs.writeFileSync(path.join(__dirname, file), content
		.filter(i => i[0] && i[1])
		.map(i => `${i[0]}=${i[1]}`)
		.sort()
		.join("\n"));
}