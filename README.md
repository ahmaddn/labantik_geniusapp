# Labantik Genius - Web Education

Labantik Genius is an interactive and gamified educational web application designed to make learning engaging and effective. The platform provides structured learning pathways through modules and missions, featuring interactive simulations, quizzes, and multimedia materials.

## 🚀 Key Features

### 1. Interactive Modules & Missions
- **Structured Learning**: Curriculum is organized into dynamic modules and progressive missions.
- **Mission Checkpoints**: Students navigate through specific tasks and objectives within each module to unlock the next level.
- **Multimedia Integration**: Supports YouTube links, custom images, and rich-text explanations to deliver interactive materials.
- **Voiceover Integration**: Supports custom voiceover tracks (e.g., in Mission 1) to provide audio-based guidance to students.

### 2. Gamified Simulations
The platform provides a highly interactive "Playground" with four unique simulation modes:
- **Slider Simulation**: Students interact with an interactive slider (e.g., adjusting water levels or temperature) to see how different parameters change the environment visually.
- **Comparison Simulation**: Side-by-side interactive views showing before/after effects or contrasting scenarios (e.g., healthy vs. polluted environment).
- **Clickable Object Exploration**: Interactive hidden-object styled tasks where students must click on positive or negative objects scattered across a scene to earn points and read their impact.
- **Case Studies (Scenarios)**: Real-world problems where students must choose the correct solution from multiple options and receive immediate, context-aware feedback.

### 3. Drag & Drop Interactions
- **Hands-on Categorization**: Engaging interactions where students categorize, organize, or sort elements properly to test their understanding dynamically.
- **Immediate Status & SFX Feedback**: Displays instant visual indicators (correct/wrong badges, ripple effects) and plays satisfying pop/feedback sound effects upon dropping items.

### 4. Assessments & Quizzes
- **Pretest & Posttest System**: Dedicated tests before and after modules to measure knowledge growth and learning effectiveness.
- **Mission-level Quizzes**: Mini-assessments tied to specific missions to validate a student's grasp of the current topic.
- **Detailed Result Dashboard**: Displays comprehensive assessment results (scores of pretest, posttest, and missions) with celebratory canvas-confetti animations and interactive mascot dialogues explaining answers.

### 5. Custom Module Design & Templates
- Administrators can customize the visual theme of the modules (mascots, backgrounds, color schemes) using built-in design templates to keep the learning environment fresh and attractive.

### 6. Interactive Copy & Audio Systems
- **Mascot Speech Bubbles**: The learning mascot speaks to the user using friendly, interactive dialog and shifts poses dynamically (jempol, keren, pikir, nunjuk).
- **Custom Dialogues**: Admin can set custom dialog scripts for each material/quiz to guide the student conversationally.
- **Sound System (useSfx & useMusic)**: A managed audio engine handles playbacks of background soundtracks and click/feedback SFX cleanly across Vue pages.

### 7. Admin & Teacher Dashboard (GeniAdmin)
- **Class & Student Management**: Import users via Excel (CSV) or add them manually. Group students into designated classes.
- **Content Management**: Create, edit, and organize all modules, missions, simulation configurations, and study materials easily via a user-friendly CMS interface.
- **Feature Toggles**: Easily activate or deactivate certain modules depending on the curriculum schedule.

### 8. Progress Tracking & Reporting
- **Student History**: Track exact login times, completion status of missions, and individual quiz scores.
- **Exportable Reports**: Generate and export detailed reports in Excel (XLSX) format for easy grading and administrative review.

## 👥 User Roles

- **Admin/Guru (GeniAdmin)**: Has full access to manage the curriculum, configure all gamified simulations, customize UI templates, manage user data, and view/export analytical reports.
- **Student (Player)**: Focuses on the learning experience. Can access the interactive playground, take pretests/posttests, complete gamified missions, and explore educational simulations.

## 🛠️ Tech Stack

- **Backend**: [Laravel 12](https://laravel.com/) (PHP)
- **Frontend**: [Vue 3](https://vuejs.org/) (Composition API) with [Inertia.js](https://inertiajs.com/)
- **Styling**: [Tailwind CSS](https://tailwindcss.com/)
- **Icons**: [Lucide Vue Next](https://lucide.dev/)
- **Charts**: [Chart.js](https://www.chartjs.org/)
- **Text Editor**: Vue Quill

## 📄 License

This project is proprietary and built specifically for the Labantik Genius educational platform.
